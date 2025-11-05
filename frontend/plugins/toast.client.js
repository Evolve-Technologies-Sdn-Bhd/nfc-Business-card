// plugins/toast.client.js
export default defineNuxtPlugin((nuxtApp) => {
  // Only run on client side
  if (process.server) return;

  // Simple toast implementation using native browser APIs
  const toast = {
    success: (message, options = {}) => {
      showToast(message, "success", options);
    },
    error: (message, options = {}) => {
      showToast(message, "error", options);
    },
    warning: (message, options = {}) => {
      showToast(message, "warning", options);
    },
    info: (message, options = {}) => {
      showToast(message, "info", options);
    },
  };

  const showToast = (message, type = "info", options = {}) => {
    const { duration = 3000, position = "top-right" } = options;

    // Create toast element
    const toastEl = document.createElement("div");
    toastEl.className = getToastClasses(type, position);
    toastEl.innerHTML = `
      <div class="flex items-center">
        <div class="flex-shrink-0">
          ${getIcon(type)}
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium">${message}</p>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
          <button class="toast-close bg-transparent rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-offset-2">
            <span class="sr-only">Close</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    `;

    // Get or create toast container
    let container = document.getElementById("toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "toast-container";
      container.className = `fixed ${getContainerPosition(
        position
      )} z-50 space-y-2 pointer-events-none`;
      document.body.appendChild(container);
    }

    // Add pointer events to toast
    toastEl.style.pointerEvents = "auto";

    // Add to container
    container.appendChild(toastEl);

    // Add close functionality
    const closeBtn = toastEl.querySelector(".toast-close");
    const closeToast = () => {
      toastEl.classList.add("opacity-0", "transform", "scale-95");
      setTimeout(() => {
        if (toastEl.parentNode) {
          toastEl.parentNode.removeChild(toastEl);
        }
        // Remove container if empty
        if (container.children.length === 0) {
          container.remove();
        }
      }, 150);
    };

    closeBtn?.addEventListener("click", closeToast);

    // Auto close
    if (duration > 0) {
      setTimeout(closeToast, duration);
    }

    // Animate in
    requestAnimationFrame(() => {
      toastEl.classList.add("opacity-100", "transform", "scale-100");
    });
  };

  const getToastClasses = (type, position) => {
    const baseClasses =
      "opacity-0 transform scale-95 transition-all duration-150 ease-in-out max-w-sm w-full shadow-lg rounded-lg p-4 pointer-events-auto";

    const typeClasses = {
      success: "bg-success-50 border border-success-200 text-success-800",
      error: "bg-error-50 border border-error-200 text-error-800",
      warning: "bg-warning-50 border border-warning-200 text-warning-800",
      info: "bg-primary-50 border border-primary-200 text-primary-800",
    };

    return `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
  };

  const getIcon = (type) => {
    const icons = {
      success:
        '<svg class="h-5 w-5 text-success-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>',
      error:
        '<svg class="h-5 w-5 text-error-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>',
      warning:
        '<svg class="h-5 w-5 text-warning-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>',
      info: '<svg class="h-5 w-5 text-primary-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>',
    };
    return icons[type] || icons.info;
  };

  const getContainerPosition = (position) => {
    const positions = {
      "top-right": "top-4 right-4",
      "top-left": "top-4 left-4",
      "bottom-right": "bottom-4 right-4",
      "bottom-left": "bottom-4 left-4",
      "top-center": "top-4 left-1/2 transform -translate-x-1/2",
      "bottom-center": "bottom-4 left-1/2 transform -translate-x-1/2",
    };
    return positions[position] || positions["top-right"];
  };

  // Provide toast to nuxt app
  nuxtApp.provide("toast", toast);
});
