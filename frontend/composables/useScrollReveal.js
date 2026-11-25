/**
 * Scroll-reveal for the public pages.
 *
 * Toggles `.is-revealed` on `[data-reveal]` elements as they enter the
 * viewport. Re-armable (`setupReveal`) so a page can call it again once
 * asynchronously rendered content — e.g. the FAQ list — has mounted.
 */
export const useScrollReveal = () => {
  let observer = null;

  const setupReveal = () => {
    if (typeof window === "undefined") return;
    const nodes = document.querySelectorAll("[data-reveal]:not(.is-revealed)");
    if (!nodes.length) return;

    const reduceMotion =
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (reduceMotion || !("IntersectionObserver" in window)) {
      nodes.forEach((el) => el.classList.add("is-revealed"));
      return;
    }

    if (!observer) {
      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add("is-revealed");
              observer.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
      );
    }

    nodes.forEach((el) => observer.observe(el));
  };

  onMounted(setupReveal);

  onUnmounted(() => {
    if (observer) {
      observer.disconnect();
      observer = null;
    }
  });

  return { setupReveal };
};
