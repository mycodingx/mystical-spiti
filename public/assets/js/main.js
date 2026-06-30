/**
 * Mystical Expedition - Frontend JavaScript
 * Vanilla ES6+, no dependencies
 */
(function () {
  "use strict";

  // -----------------------------------------------------------------
  // 1. Modal management
  // -----------------------------------------------------------------
  const modals = document.querySelectorAll(".me-modal");

  function openModal(id, prefill) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";

    // Pre-fill destination if data attribute is set
    if (prefill) {
      const sel = modal.querySelector("[data-prefill-destination]");
      if (sel) sel.value = prefill;
    }

    // Focus first input
    const firstInput = modal.querySelector(
      'input:not(.me-honeypot):not([type="hidden"])',
    );
    if (firstInput) firstInput.focus({ preventScroll: true });
  }

  function closeModal(modal) {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  function closeAllModals() {
    modals.forEach(closeModal);
  }

  // Open triggers
  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-open-modal]");
    if (trigger) {
      e.preventDefault();
      const id = trigger.getAttribute("data-open-modal");
      const prefill = trigger.getAttribute("data-prefill") || "";
      openModal(id, prefill);
      return;
    }

    const closeBtn = e.target.closest("[data-close-modal]");
    if (closeBtn) {
      const modal = closeBtn.closest(".me-modal");
      if (modal) closeModal(modal);
    }
  });

  // ESC closes any open modal
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAllModals();
  });

  // Trap focus inside modal (basic)
  modals.forEach((modal) => {
    modal.addEventListener("keydown", (e) => {
      if (e.key !== "Tab") return;
      const focusable = modal.querySelectorAll(
        "button:not([disabled]), input:not([disabled]):not(.me-honeypot), select:not([disabled]), textarea:not([disabled]), a[href]",
      );
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    });
  });

  // -----------------------------------------------------------------
  // 2. AJAX form submission
  // -----------------------------------------------------------------
  async function submitForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const message = form.querySelector("[data-form-message]");
    const originalHtml = submitBtn.innerHTML;

    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    // Clear previous message
    if (message) {
      message.className = "me-form__message";
      message.textContent = "";
    }

    try {
      const formData = new FormData(form);
      const response = await fetch(form.action || "submit.php", {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
        credentials: "same-origin",
      });

      let data = {};
      try {
        data = await response.json();
      } catch (_) {
        /* response not JSON */
      }

      if (response.ok && data.ok) {
        if (message) {
          message.className = "me-form__message is-success";
          message.textContent =
            data.message || "Thank you! We will contact you shortly.";
        }
        // Reset form
        form.reset();
        // Resolve redirect URL relative to the current page so the
        // site works at host root (https://shimla.mysticalexpedition.com/)
        // and under a subdirectory (http://localhost:8080/mystical/).
        const thanksUrl = new URL("thanks.php", window.location.href).href;

        // Optional: close modal after 1.5s if inside one
        const modal = form.closest(".me-modal");
        if (modal) {
          setTimeout(() => {
            closeModal(modal);
            // Redirect to thanks page
            window.location.href = thanksUrl;
          }, 1200);
        } else {
          // Inline form (hero) — redirect
          setTimeout(() => {
            window.location.href = thanksUrl;
          }, 800);
        }

        // Analytics event
        if (window.dataLayer) {
          window.dataLayer.push({
            event: "generate_lead",
            form_location: modal ? "modal" : "hero",
          });
        }
      } else {
        const errorText =
          (data.errors && data.errors._form) ||
          data.message ||
          "Submission failed. Please try again.";
        if (message) {
          message.className = "me-form__message is-error";
          message.textContent = errorText;
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;

        // Mark individual field errors
        if (data.errors) {
          Object.entries(data.errors).forEach(([name, msg]) => {
            if (name === "_form") return;
            const field = form.querySelector(`[name="${name}"]`);
            if (field) field.classList.add("is-invalid");
          });
        }
      }
    } catch (err) {
      if (message) {
        message.className = "me-form__message is-error";
        message.textContent = "Network error. Please try again.";
      }
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHtml;
    }
  }

  document.addEventListener("submit", (e) => {
    const form = e.target.closest("[data-ajax-form]");
    if (!form) return;
    e.preventDefault();
    submitForm(form);
  });

  // Clear field validation on input
  document.addEventListener("input", (e) => {
    if (e.target.classList && e.target.classList.contains("is-invalid")) {
      e.target.classList.remove("is-invalid");
    }
  });

  // -----------------------------------------------------------------
  // 3. Exit-intent (desktop only)
  // -----------------------------------------------------------------
  const exitModal = document.getElementById("exitIntentModal");
  if (exitModal && window.innerWidth > 992) {
    let fired = false;
    const storageKey = "me_exit_intent_shown";

    // Skip if already shown this session
    try {
      if (sessionStorage.getItem(storageKey)) fired = true;
    } catch (_) {
      /* sessionStorage may be blocked */
    }

    // Wait 20s after page load before allowing trigger
    setTimeout(() => {
      if (fired) return;
      document.addEventListener("mouseout", (e) => {
        if (e.clientY < 10 && !fired) {
          fired = true;
          try {
            sessionStorage.setItem(storageKey, "1");
          } catch (_) {}
          openModal("exitIntentModal");
        }
      });
    }, 20000);
  }

  // -----------------------------------------------------------------
  // 4. Smooth scroll for in-page anchors
  // -----------------------------------------------------------------
  document.addEventListener("click", (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    const href = link.getAttribute("href");
    if (href === "#" || href.length < 2) return;
    const target = document.querySelector(href);
    if (target) {
      e.preventDefault();
      const top = target.getBoundingClientRect().top + window.pageYOffset - 80;
      window.scrollTo({ top, behavior: "smooth" });
    }
  });

  // -----------------------------------------------------------------
  // 5. Header shadow on scroll
  // -----------------------------------------------------------------
  const header = document.querySelector(".me-top-header");
  if (header) {
    let lastY = 0;
    window.addEventListener(
      "scroll",
      () => {
        const y = window.scrollY;
        if (y > 10 && lastY <= 10)
          header.style.boxShadow = "0 4px 12px rgba(0,0,0,0.08)";
        else if (y <= 10 && lastY > 10) header.style.boxShadow = "";
        lastY = y;
      },
      { passive: true },
    );
  }
})();
