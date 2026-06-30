/**
 * Mystical Expedition - Client-side form validation
 * Provides instant feedback; server-side validation is authoritative.
 */
(function () {
  "use strict";

  const PHONE_RE = /^[6-9]\d{9}$/;
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const NAME_RE = /^[\p{L}\p{M}\s'\-\.]+$/u;

  function setInvalid(input, message) {
    input.classList.add("is-invalid");
    input.setAttribute("aria-invalid", "true");

    let hint = input.parentElement.querySelector(".me-form__hint");
    if (!hint) {
      hint = document.createElement("div");
      hint.className = "me-form__hint";
      hint.style.cssText = "color:#ef4444;font-size:12px;margin-top:4px;";
      input.parentElement.appendChild(hint);
    }
    hint.textContent = message;
  }

  function setValid(input) {
    input.classList.remove("is-invalid");
    input.removeAttribute("aria-invalid");
    const hint = input.parentElement.querySelector(".me-form__hint");
    if (hint) hint.remove();
  }

  function validateField(input) {
    const value = (input.value || "").trim();
    const name = input.name;

    // Honeypot - always valid (silently ignored)
    if (name === "website") return true;

    // Required check
    if (input.required && !value) {
      setInvalid(input, "This field is required.");
      return false;
    }

    switch (name) {
      case "name":
        if (value.length < 2 || value.length > 100) {
          setInvalid(input, "Enter your full name (2–100 characters).");
          return false;
        }
        if (!NAME_RE.test(value)) {
          setInvalid(input, "Name contains invalid characters.");
          return false;
        }
        break;
      case "city":
        if (value.length < 2 || value.length > 60) {
          setInvalid(input, "Enter your city (2–60 characters).");
          return false;
        }
        break;
      case "email":
        if (!EMAIL_RE.test(value) || value.length > 100) {
          setInvalid(input, "Enter a valid email address.");
          return false;
        }
        break;
      case "phone":
        const digits = value.replace(/\D/g, "");
        if (!PHONE_RE.test(digits)) {
          setInvalid(input, "Enter a valid 10-digit Indian mobile number.");
          return false;
        }
        if (digits !== value) input.value = digits; // Normalise
        break;
      case "destination":
        if (!value) {
          setInvalid(input, "Please select a destination.");
          return false;
        }
        break;
    }

    setValid(input);
    return true;
  }

  function attachValidation(form) {
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      if (input.name === "website") return; // skip honeypot
      input.addEventListener("blur", () => {
        if (input.value.trim()) validateField(input);
      });
      input.addEventListener("input", () => {
        if (input.classList.contains("is-invalid")) validateField(input);
      });
    });

    form.addEventListener("submit", (e) => {
      // Skip if AJAX will handle it
      if (form.hasAttribute("data-ajax-form")) {
        let firstInvalid = null;
        inputs.forEach((input) => {
          if (input.name === "website") return;
          const ok = validateField(input);
          if (!ok && !firstInvalid) firstInvalid = input;
        });
        if (firstInvalid) {
          e.preventDefault();
          firstInvalid.focus();
        }
      }
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("form.me-form").forEach(attachValidation);
  });
})();
