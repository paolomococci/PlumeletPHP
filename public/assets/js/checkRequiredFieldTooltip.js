/**  
 * checkRequiredFieldTooltip.js
 *
 *  A tiny ES-module that wires the three validators
 * (required fields, e-mail and password) to any `<form>`
 * and, whenever the form is not valid, displays a
 * centered tooltip with a human-readable list of the missing
 * / invalid fields. The tooltip is fully responsive - it works
 * on desktop, laptop, tablet, palm-tablet and small smartphones -
 * and disappears as soon as the form becomes valid again.
 *
 * Usage (ES-module import):
 *
 *   import { attachTooltip } from '/assets/js/checkRequiredFieldTooltip.js';
 *
 *   Attach to a single form (or pass a selector)
 *   attachTooltip('#postForm');
 *
 *   Attach to many forms
 *   document.querySelectorAll('form').forEach(f => attachTooltip(f));
 *
 */

import { initCheckRequiredField } from '/assets/js/checkRequiredField.js';
import { initEmailValidator } from '/assets/js/validateEmail.js';
import { initPasswordValidator } from '/assets/js/validatePassword.js';

/** 
 * Helper: build a human-readable description of a form element.
 *
 * The function is used whenever the library needs to display a field
 * name (for example, in validation error messages).  It follows a
 * clear priority order so that the most descriptive, user-friendly
 * label is returned whenever possible.
 *
 * @param {HTMLElement} el - The form element to describe.
 * 
 * @returns {string} A human-readable name for the element.
 */
function getDescription(el) {
    // If no element is provided, fall back to a generic label.
    // Handle the edge case where the element is falsy (e.g., `null`, `undefined`). 
    // In that situation it return a generic placeholder.
    if (!el) return 'Unnamed field';
    // Try to use the element’s ID first.
    const id = el.id;
    if (id) {
        // If an associated `<label>` exists, use its text content.
        const label = document.querySelector(`label[for="${id}"]`);
        if (label) return label.textContent.trim();
    }
    // Fallback to the element’s `name` attribute or the ID again.
    return el.name || id || 'Unnamed field';
}

// Tooltip const.
const TOOLTIP_ID = 'required-field-tooltip';
const TOOLTIP_CLOSE = 'rf-tooltip-close';
const TOOLTIP_LIST = 'rf-tooltip-list';

/**
 * Retrieves the tooltip element that is used to show validation messages.
 * If it does not yet exist, it is created and configured.
 *
 * @returns {HTMLElement} The tooltip <div> that will be shown or hidden.
 */
function getTooltip() {
    // Try to find an existing tooltip by its known ID.
    let tooltip = document.getElementById(TOOLTIP_ID);

    // If we didn't find one, we must build the tooltip from scratch.
    if (!tooltip) {
        // Create a new <div> that will act as the tooltip container.
        tooltip = document.createElement('div');
        // Give it a stable ID for future look-ups.
        tooltip.id = TOOLTIP_ID;
        // Base styling class.
        tooltip.className = 'rf-tooltip';
        // Accessibility: announce content changes.
        tooltip.setAttribute('role', 'alert');
        // Build the inner markup: an unordered list for messages 
        // and a close button (x) that the user can click to dismiss.
        tooltip.innerHTML = `
            <ul class="${TOOLTIP_LIST}"></ul>
            <button class="${TOOLTIP_CLOSE}" aria-label="Close">&times;</button>
        `;
        // Append the tooltip to the document body so it becomes part of the DOM.
        document.body.appendChild(tooltip);
        // Initially hide the tooltip; it will be shown only when needed.
        tooltip.style.display = 'none';

        // Close button → hide the tooltip when clicked.
        tooltip
            .querySelector(`.${TOOLTIP_CLOSE}`)
            .addEventListener('click', hideTooltip);

        // Prevent any click that happens inside the tooltip from bubbling
        // up to the document. This stops accidental page navigation or
        // other global click handlers from being triggered while the
        // tooltip is open.
        tooltip.addEventListener('click', e => e.stopPropagation());
    }

    // Return the (existing or newly created) tooltip element.
    return tooltip;
}

/**
 * Show the tooltip with the supplied list of error strings.
 * 
 * @param {HTMLFormElement} form
 * @param {string[]} errors
 */
function showTooltip(form, errors) {
    if (!errors.length) return;

    const tooltip = getTooltip();
    const list = tooltip.querySelector(`.${TOOLTIP_LIST}`);
    list.innerHTML = '';
    errors.forEach(err => {
        const li = document.createElement('li');
        // Remove leading dash if present (our validators sometimes prepend “- ”).
        li.textContent = err.replace(/^-\s*/, '');
        list.appendChild(li);
    });

    // Position the tooltip over the form (centered).
    const rect = form.getBoundingClientRect();
    // 40 half tooltip height.
    const top = rect.top + window.scrollY + rect.height / 2 - 40;
    const left = rect.left + rect.width / 2;

    tooltip.style.display = 'block';
    tooltip.style.top = `${top}px`;
    tooltip.style.left = `${left}px`;
    tooltip.style.transform = 'translate(-50%, -50%)';

    // Keep tooltip inside the viewport on very small screens.
    const vp = { width: window.innerWidth, height: window.innerHeight };
    const tooltipRect = tooltip.getBoundingClientRect();

    if (tooltipRect.right > vp.width) {
        tooltip.style.left = `${vp.width - tooltipRect.width / 2 - 20}px`;
        tooltip.style.transform = 'translateX(-50%)';
    }
    if (tooltipRect.left < 0) {
        tooltip.style.left = `${tooltipRect.width / 2 + 20}px`;
        tooltip.style.transform = 'translateX(0)';
    }
    if (tooltipRect.bottom > vp.height) {
        tooltip.style.top = `${vp.height - tooltipRect.height / 2 - 20}px`;
        tooltip.style.transform = 'translate(-50%, -50%)';
    }
    if (tooltipRect.top < 0) {
        tooltip.style.top = `${tooltipRect.height / 2 + 20}px`;
        tooltip.style.transform = 'translate(-50%, 0)';
    }

    // Auto-hide after 5 sec, unless the user fixes the form earlier.
    if (!tooltip.dataset.timeout) {
        tooltip.dataset.timeout = setTimeout(hideTooltip, 5000);
    }
}

/**
 * Hide the tooltip and clear any pending timeout.
 */
function hideTooltip() {
    const tooltip = document.getElementById(TOOLTIP_ID);
    if (!tooltip) return;
    tooltip.style.display = 'none';
    if (tooltip.dataset.timeout) {
        clearTimeout(Number(tooltip.dataset.timeout));
        delete tooltip.dataset.timeout;
    }
}

/** 
 * Public API - attach the tooltip to any form.
 */
export function attachTooltip(formOrSelector, opts = {}) {
    const form = typeof formOrSelector === 'string'
        ? document.querySelector(formOrSelector)
        : formOrSelector;

    if (!form || !(form instanceof HTMLFormElement)) {
        console.warn('[RF] attachTooltip() - invalid selector / element');
        return;
    }

    // -----------------------------------------------------------------
    // Initialize the three validators (required, email, password)
    // -----------------------------------------------------------------
    const requiredCtrl = initCheckRequiredField(form);
    const emailCtrl = initEmailValidator(form);
    const pwdCtrl = initPasswordValidator(form);
    const submitBtn = form.querySelector('[type="submit"]');

    /**
     * Runs a full form validation cycle and updates the UI accordingly.
     *
     * The function is called when the user tries to submit the form.  It
     * performs the following steps in order:
     *
     * 1.  Ask each individual control validator to re-evaluate its
     *     current value.  The validator objects (`requiredCtrl`,
     *     `emailCtrl`, `pwdCtrl`) each expose a `validate()` method that
     *     mutates their internal `errors` array.
     *
     * 2.  Collect the error messages from all three validators into one
     *     flat array (`allErrors`).  The spread syntax is used to concatenate
     *     the individual `errors` arrays.
     *
     * 3.  Enable or disable the form’s submit button based on whether
     *     there are any errors.  If the array is non-empty the button
     *     is disabled to prevent a bad submission.
     *
     * 4.  If there are any errors, display them in a tooltip positioned
     *     near the form and return `false` to cancel the submission.
     *
     * 5.  If there are no errors, hide any existing tooltip and return
     *     `true` so the form can be submitted normally.
     * 
     */
    function validateAll() {
        // Refresh each validator - they recalculate their `errors` array 
        // based on the current value of the form control.
        requiredCtrl.validate();
        emailCtrl.validate();
        pwdCtrl.validate();

        // Merge all individual error arrays into one flat list.
        const allErrors = [
            ...requiredCtrl.errors,
            ...emailCtrl.errors,
            ...pwdCtrl.errors,
        ];

        // Enable/Disable the submit button according to error count.
        if (submitBtn) submitBtn.disabled = allErrors.length > 0;

        // If any errors exist, show them in the tooltip and block
        // the form submission by returning `false`.
        if (allErrors.length) {
            showTooltip(form, allErrors);
            return false;
        }

        // No errors - hide the tooltip (if it was shown) and allow
        // the form to submit by returning `true`.
        hideTooltip();
        return true;
    }

    // Initial validation so the button state is correct on page load
    validateAll();

    // -----------------------------------------------------------------
    // Event listeners
    // -----------------------------------------------------------------

    // Submit - block if the form is not valid.
    form.addEventListener('submit', e => {
        // Keep tooltip visible.
        if (!validateAll()) e.preventDefault();
    });

    // Input, re-validate on every change to required / email / password fields.
    form.addEventListener('input', e => {
        const target = e.target;
        if (
            target.matches('[required]') ||
            target.type === 'email' ||
            target.type === 'password'
        ) {
            // Updates tooltip and button state.
            validateAll();
        }
    });

    // Show tooltip when the user hover/focus a disabled submit button.
    if (submitBtn) {
        const maybeShow = () => {
            if (submitBtn.disabled) {
                const errors = [
                    ...requiredCtrl.errors,
                    ...emailCtrl.errors,
                    ...pwdCtrl.errors,
                ];
                showTooltip(form, errors);
            }
        };
        submitBtn.addEventListener('mouseenter', maybeShow);
        submitBtn.addEventListener('focus', maybeShow);
    }

    // Click outside the tooltip, hide it.
    document.addEventListener('click', e => {
        const tooltip = document.getElementById(TOOLTIP_ID);
        if (tooltip && !tooltip.contains(e.target)) hideTooltip();
    });
}

/**
 * Dynamically injects the CSS file that styles the tooltip.
 *
 * @returns {void} This function does not return a value.
 */
export function loadTooltipStyles() {
    // Create a <link> element that will reference a stylesheet.
    const link = document.createElement('link');
    // Tell the browser that this link is a stylesheet.
    link.rel = 'stylesheet';
    // Provide the relative path to the tooltip's CSS file.
    link.href = '/assets/css/required_field_tooltip.css';
    // Append the <link> element to the <head> so the 
    // styles are loaded and applied to the page.
    document.head.appendChild(link);
}