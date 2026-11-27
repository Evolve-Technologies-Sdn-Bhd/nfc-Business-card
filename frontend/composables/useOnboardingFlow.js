/**
 * useOnboardingFlow
 *
 * Single source of truth for the self-serve onboarding funnel:
 *   /Onboarding/plan -> /Onboarding/design -> /Onboarding/checkout -> /Onboarding/complete
 *
 * The draft is mirrored in two places on purpose:
 *   • sessionStorage — the legacy key names (selectedPlan / cardInfo / cardDesign /
 *     selectedTemplate / designMethod) are still written so every existing reader
 *     keeps working, and a hard refresh inside the same tab never loses data.
 *   • the server     — users.settings.onboarding_draft, so the user can resume
 *     after closing the tab or switching device.
 *
 * The server draft is a convenience only: every network call in here is
 * best-effort and must never block the funnel.
 */

export const ONBOARDING_STEPS = ["plan", "design", "checkout"];

export const ONBOARDING_STEP_LABELS = {
  plan: "Plan",
  design: "Design",
  checkout: "Checkout",
};

/** Draft keys that are mirrored to the server. */
const DRAFT_KEYS = [
  "cardInfo",
  "cardDesign",
  "selectedTemplate",
  "designMethod",
  "shipping",
];

/** Legacy sessionStorage keys that the rest of the app still reads. */
const LEGACY_KEYS = [
  "selectedPlan",
  "cardInfo",
  "cardDesign",
  "selectedTemplate",
  "designMethod",
];

/**
 * One-shot lock used by the plan step to auto-advance on a plan chosen earlier
 * on the pricing page (?plan=basic). Keyed by the plan value so browser Back or
 * a refresh never bounces the visitor forward a second time.
 */
export const PLAN_AUTORUN_KEY = "onboarding.plan.autoadvance";

export const useOnboardingFlow = () => {
  const { $api } = useNuxtApp();
  const authStore = useAuthStore();

  const plan = useState("onboarding.plan", () => null);
  const draft = useState("onboarding.draft", () => ({}));
  const hydrated = useState("onboarding.hydrated", () => false);

  /**
   * Guest checkout: a visitor with no account may still walk the funnel, but the
   * server draft and every upload must wait until an account (and its token)
   * exists. They are replayed in one shot by flushDraftToServer().
   */
  const isGuest = computed(() => !authStore.isAuthenticated);

  /**
   * Binary files parked until the guest account exists — File objects cannot be
   * serialized into sessionStorage, so they only survive for this tab session.
   */
  const pendingFiles = useState("onboarding.pendingFiles", () => ({
    logo: null,
    front: null,
    back: null,
  }));

  /**
   * The plan the funnel should act on.
   *
   * While a user is onboarding the plan they just picked is authoritative.
   * Outside onboarding (e.g. an existing customer ordering an extra card from
   * the dashboard) we fall back to the account's own plan — but never for a
   * brand-new user, who still has to pick one.
   */
  const activePlan = computed(() => {
    if (plan.value) return plan.value;
    if (authStore.user?.is_new_user) return null;
    return authStore.user?.subscription_plan || null;
  });

  /** Plan the design step should render — the picked one wins over the account. */
  const planForDesign = computed(
    () => activePlan.value || authStore.user?.subscription_plan || "free"
  );

  // ------------------------------------------------------------- local mirror
  const readLocal = () => {
    if (!process.client) return {};
    const out = {};
    const get = (key) => sessionStorage.getItem(key);

    if (get("selectedPlan")) out.plan = get("selectedPlan");
    for (const key of ["cardInfo", "cardDesign"]) {
      const raw = get(key);
      if (!raw) continue;
      try {
        out[key] = JSON.parse(raw);
      } catch {
        // Ignore a corrupt value — the server draft can still fill the gap.
      }
    }
    if (get("selectedTemplate")) out.selectedTemplate = get("selectedTemplate");
    if (get("designMethod")) out.designMethod = get("designMethod");
    return out;
  };

  const writeLocal = () => {
    if (!process.client) return;
    if (plan.value) sessionStorage.setItem("selectedPlan", plan.value);
    if (draft.value.cardInfo)
      sessionStorage.setItem("cardInfo", JSON.stringify(draft.value.cardInfo));
    if (draft.value.cardDesign)
      sessionStorage.setItem("cardDesign", JSON.stringify(draft.value.cardDesign));
    if (draft.value.selectedTemplate)
      sessionStorage.setItem("selectedTemplate", draft.value.selectedTemplate);
    if (draft.value.designMethod)
      sessionStorage.setItem("designMethod", draft.value.designMethod);
  };

  // ---------------------------------------------------------- hydrate / save
  const hydrate = async () => {
    if (hydrated.value) return;

    const local = readLocal();
    if (local.plan) plan.value = plan.value || local.plan;

    const localDraft = {};
    DRAFT_KEYS.forEach((key) => {
      if (local[key]) localDraft[key] = local[key];
    });
    draft.value = { ...localDraft, ...draft.value };

    if (!isGuest.value) {
      try {
        const res = await $api.get("/onboarding/draft");
        const server = res?.draft || {};
        if (!plan.value && server.plan) plan.value = server.plan;
        DRAFT_KEYS.forEach((key) => {
          if (server[key] && !draft.value[key]) draft.value[key] = server[key];
        });
      } catch {
        // Best-effort: an unavailable draft must not block onboarding.
      }
    }

    hydrated.value = true;
    writeLocal();
  };

  const patchDraft = (patch = {}) => {
    draft.value = { ...draft.value, ...patch };
    writeLocal();
  };

  const saveDraft = async () => {
    writeLocal();
    if (isGuest.value) return;
    try {
      const payload = { plan: plan.value };
      DRAFT_KEYS.forEach((key) => {
        if (draft.value[key] !== undefined) payload[key] = draft.value[key];
      });
      await $api.put("/onboarding/draft", payload);
    } catch {
      // Best-effort: sessionStorage already holds the draft for this tab.
    }
  };

  const setPlan = async (value) => {
    plan.value = value;
    await saveDraft();
  };

  const clearDraft = async () => {
    plan.value = null;
    draft.value = {};
    hydrated.value = false;
    if (process.client) {
      LEGACY_KEYS.forEach((key) => sessionStorage.removeItem(key));
      sessionStorage.removeItem(PLAN_AUTORUN_KEY);
    }
    clearPendingFiles();
    if (isGuest.value) return;
    try {
      await $api.delete("/onboarding/draft");
    } catch {
      // Best-effort.
    }
  };

  // ------------------------------------------------- pending binary uploads
  const setPendingFile = (kind, file) => {
    pendingFiles.value = { ...pendingFiles.value, [kind]: file };
  };

  const clearPendingFiles = () => {
    pendingFiles.value = { logo: null, front: null, back: null };
  };

  /**
   * Replay everything the guest captured locally onto the account that was just
   * created at checkout. Called ONCE, right after the guest-checkout/start token
   * is in place and before /payment/initiate is fired.
   *
   * Everything here is best-effort: the payment must never be blocked by a
   * failed mirror — the same data also travels inside the payment metadata.
   */
  const flushDraftToServer = async () => {
    if (isGuest.value) return;

    try {
      await saveDraft();
    } catch {
      // ignore
    }

    const info = draft.value.cardInfo;
    if (info) {
      try {
        await $api.post("/onboarding/save-card-info", {
          name: info.name,
          position: info.position,
          contactNumber: info.contactNumber,
          email: info.email,
          website: info.website,
          address: info.address,
          companyLogo: info.companyLogo,
          companyBackground: info.companyBackground,
          services: info.services,
          linkedin: info.linkedin,
          twitter: info.twitter,
          facebook: info.facebook,
          instagram: info.instagram,
        });
      } catch {
        // ignore — card_info also ships inside the payment metadata
      }
    }

    const files = pendingFiles.value;

    if (files.logo) {
      try {
        const formData = new FormData();
        formData.append("logo", files.logo);
        const res = await $api.post("/upload/company-logo", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        if (res?.success && res?.data?.url) {
          patchDraft({
            cardInfo: { ...(draft.value.cardInfo || {}), companyLogo: res.data.url },
          });
        }
      } catch {
        // ignore
      }
    }

    if (files.front && files.back) {
      try {
        const formData = new FormData();
        formData.append("front_design", files.front);
        formData.append("back_design", files.back);
        formData.append("design_method", draft.value.designMethod || "custom");
        formData.append("selected_template", draft.value.selectedTemplate || "");
        await $api.post("/onboarding/upload-card-design", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });
      } catch {
        // ignore
      }
    }

    clearPendingFiles();
  };

  // -------------------------------------------------------- routing helpers
  const stepNumber = (name) => {
    const index = ONBOARDING_STEPS.indexOf(name);
    return index === -1 ? 1 : index + 1;
  };

  /** Where /Onboarding (and the whole funnel) should send the user right now. */
  const resolveEntry = () => {
    const effective = activePlan.value;
    if (!effective) return "/Onboarding/plan";
    if (effective === "free") return "/Onboarding/complete";
    if (!draft.value.cardInfo) return "/Onboarding/design";
    return "/Onboarding/checkout";
  };

  /** Guard for one step: returns a redirect path, or null when the step is allowed. */
  const guardFor = (step) => {
    const effective = activePlan.value;
    if (!effective) return "/Onboarding/plan";
    if (step === "design" && effective === "free") return "/Onboarding/complete";
    if (step === "checkout" && !draft.value.cardInfo) return "/Onboarding/design";
    return null;
  };

  return {
    plan,
    draft,
    hydrated,
    activePlan,
    planForDesign,
    isGuest,
    pendingFiles,
    hydrate,
    setPlan,
    patchDraft,
    saveDraft,
    clearDraft,
    setPendingFile,
    clearPendingFiles,
    flushDraftToServer,
    stepNumber,
    resolveEntry,
    guardFor,
  };
};
