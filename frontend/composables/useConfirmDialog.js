// composables/useConfirmDialog.js
//
// Promise-based replacement for window.confirm() that renders the shared
// <AdminConfirmDialog>. Usage in a page:
//
//   const { confirmState, askConfirm, resolveConfirm } = useConfirmDialog();
//   if (!(await askConfirm({ title, message, confirmLabel, tone: "danger" }))) return;
//
//   <AdminConfirmDialog v-model="confirmState.open" :title="confirmState.title" ...
//     @confirm="resolveConfirm(true)" @cancel="resolveConfirm(false)" />

export const useConfirmDialog = () => {
  const confirmState = reactive({
    open: false,
    title: "",
    message: "",
    confirmLabel: "Confirm",
    cancelLabel: "Cancel",
    tone: "primary",
  });
  let resolver = null;

  const askConfirm = (opts = {}) =>
    new Promise((resolve) => {
      // A dialog left open by a previous call resolves as "cancelled".
      resolver?.(false);
      Object.assign(confirmState, {
        title: "Are you sure?",
        message: "",
        confirmLabel: "Confirm",
        cancelLabel: "Cancel",
        tone: "primary",
        ...opts,
        open: true,
      });
      resolver = resolve;
    });

  const resolveConfirm = (value) => {
    confirmState.open = false;
    resolver?.(value);
    resolver = null;
  };

  return { confirmState, askConfirm, resolveConfirm };
};
