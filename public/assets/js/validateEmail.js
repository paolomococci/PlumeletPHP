/**
 * validateEmail.js
 *
 *
 * Tiny helper that validates every `<input type="email">` inside a form.
 *
 * Features:
 *   • Optional enforcement of the `required` attribute.
 *   • Native HTML5 validation when available.
 *   • Fallback regex validation for browsers that lack `checkValidity`.
 *   • Caches the list of email inputs after the first run.
 *   • Adds `aria-invalid="true"` to invalid fields (accessibility).
 *   • Exposes errors globally via `window.emailValidationErrors`.
 *
 * Usage:
 *   import { initEmailValidator } from '/assets/js/validateEmail.js';
 *   const validator = initEmailValidator('#postForm', true); // true - enforce required
 *   const { valid, errors } = validator.validate();
 */

/** --------------------------------------------------------------------------
 * Global key used to expose validation errors to legacy code or other
 * scripts that might need to access the error list without interacting
 * with the validator instance.
 * --------------------------------------------------------------------------- */
const GLOBAL_KEY = 'emailValidationErrors';

/** --------------------------------------------------------------------------
 * Messages used by the validator.
 * These are functions so that they can be customized or localized later.
 * --------------------------------------------------------------------------- */
const messages = {
    missingRequiredAttr: (desc) =>
        `Required email field "${desc}" is missing the 'required' attribute.`,
    emptyRequired: (desc) => `Required email field "${desc}" is empty.`,
    invalidPattern: (desc) => `Email field "${desc}" contains an invalid email address.`,
};

/** ---------------------------------------------------------------------------
 * A simple yet reliable fallback regex that matches most RFC-5322 compliant
 * email addresses. It is deliberately conservative to avoid false positives.
 * --------------------------------------------------------------------------- */
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/** ---------------------------------------------------------------------------
 * Feature detection for native HTML5 email validation.
 * If the browser implements `HTMLInputElement.checkValidity`, we can rely
 * on it to perform the built-in pattern check, MIME type enforcement,
 * and other edge cases that a custom regex might miss.
 * --------------------------------------------------------------------------- */
const nativeSupported = typeof document.createElement('input').checkValidity === 'function';

/**
 * Utility: builds a human-readable description for a given input element.
 *
 * The description is used in all error messages so that the user can
 * quickly identify which field failed validation.
 *
 * 1. If the element has an `id`, we try to find a `<label>` with a
 *    matching `for` attribute and use its trimmed text content.
 * 2. If no label is found, we fall back to the element's `name`.
 * 3. If neither is present, we use the `id` (which might be empty).
 * 4. If all are missing, we return a generic placeholder.
 *
 * @param {HTMLInputElement} el
 * 
 * @returns {string}
 */
function getFieldDescription(el) {
    if (!el) return 'Unnamed field';
    const id = el.id;
    if (id) {
        const label = document.querySelector(`label[for="${id}"]`);
        if (label) return label.textContent.trim();
    }
    return el.name || id || 'Unnamed field';
}

/**
 * Validates a single `<input type="email">` element.
 *
 * Validation steps (in order):
 *   1. If `enforceRequired` is true, ensure the element actually carries
 *      the `required` attribute; if not, return a specific error.
 *   2. If the element is marked as `required` by the browser and its
 *      trimmed value is empty, return an error indicating an empty field.
 *   3. If a value is present, perform two checks:
 *        a. Native check: call `el.checkValidity()` if the browser supports
 *           it. This covers the HTML5 pattern and type checks.
 *        b. Regex check: test the value against `EMAIL_REGEX` for fallback
 *           support in browsers that do not implement `checkValidity`.
 *      Both checks must succeed; otherwise we return a pattern-invalid error.
 *
 * The function returns an object `{ valid: boolean, msg?: string }`.
 * 
 * @param {HTMLInputElement} el
 * @param {boolean} enforceRequired
 * 
 * @returns {{valid:boolean, msg?:string}}
 */
function validateSingleEmail(el, enforceRequired = false) {
    const desc = getFieldDescription(el);

    // 1. Enforce presence of the `required` attribute (optional).
    if (enforceRequired && !el.hasAttribute('required')) {
        return { valid: false, msg: messages.missingRequiredAttr(desc) };
    }

    // 2. Empty check for required fields.
    if (el.required && !el.value.trim()) {
        return { valid: false, msg: messages.emptyRequired(desc) };
    }

    // 3. If a value is present, run validation.
    if (el.value) {
        const nativeOk = nativeSupported ? el.checkValidity() : true;
        const regexOk = EMAIL_REGEX.test(el.value);

        // Both native and regex must succeed.
        if (!nativeOk || !regexOk) {
            return { valid: false, msg: messages.invalidPattern(desc) };
        }
    }

    // All checks passed.
    return { valid: true };
}

/**
 * Scans a `<form>` for all `<input type="email">` elements, validates each,
 * and collects any error messages into an array.
 *
 * The function uses a *cache* on the form element (`_emailInputs`) so that
 * the expensive `querySelectorAll` operation is performed only once.
 * Subsequent calls reuse the cached array, improving performance on large
 * forms or when validation is invoked repeatedly (e.g., during a live
 * change event).
 *
 * For each element:
 *   - We call `validateSingleEmail` to get a result.
 *   - If the result is invalid, we push the message to the `errors`
 *     array and mark the element with `aria-invalid="true"` to provide
 *     visual feedback for assistive technologies.
 *   - If the element passes validation, we remove any existing
 *     `aria-invalid` attribute.
 *
 * @param {HTMLFormElement} form
 * @param {boolean} enforceRequired
 * 
 * @returns {Array<string>}
 */
export function findEmailErrors(form, enforceRequired = false) {
    if (!(form instanceof HTMLFormElement)) {
        console.warn('validateEmail.js: Provided element is not a form.');
        return [];
    }

    // Cache the NodeList after the first call.
    if (!form._emailInputs) {
        form._emailInputs = Array.from(form.querySelectorAll('input[type="email"]'));
    }

    const errors = [];

    form._emailInputs.forEach((el) => {
        const result = validateSingleEmail(el, enforceRequired);
        if (!result.valid) {
            errors.push(result.msg);
            el.setAttribute('aria-invalid', 'true');
        } else {
            el.removeAttribute('aria-invalid');
        }
    });

    return errors;
}

/**
 * Constructs a validator object for a specific form. The object contains
 * a live `errors` array that can be queried via the `validate()` method.
 *
 * The returned `validate()` method:
 *   - Calls `findEmailErrors` to recompute the current error list.
 *   - Updates the internal `errors` property so that callers can read it
 *     without executing validation again.
 *   - Exposes the error list on the global `window` under `GLOBAL_KEY`
 *     for legacy or external scripts that cannot use the validator instance.
 *   - Returns an object `{ valid: boolean, errors: Array<string> }` so
 *     that callers can easily check the result and iterate over the
 *     messages if needed.
 *
 * An initial validation is run immediately so that the object starts
 * in a consistent state.
 *
 * @param {HTMLFormElement} form
 * @param {boolean} enforceRequired
 * 
 * @returns {{errors:Array<string>, validate:()=>{valid:boolean, errors:Array<string>}}}
 */
function createEmailValidator(form, enforceRequired) {
    const validator = {
        errors: [],

        validate() {
            const errors = findEmailErrors(form, enforceRequired);
            validator.errors = errors;
            // Global exposure for legacy code.
            window[GLOBAL_KEY] = errors;
            return { valid: errors.length === 0, errors };
        },
    };

    // Initial validation so the state is ready immediately.
    validator.validate();
    return validator;
}

/**
 * Public helper that can accept either a CSS selector string or a direct
 * `<form>` element. It normalizes the input into a real form reference
 * and then hands it off to `createEmailValidator`.
 *
 * Error handling:
 *   - If the selector resolves to `null` or the passed element is not
 *     an `<HTMLFormElement>`, we log a warning and return `null`.
 *
 * @param {string|HTMLElement} formOrSelector
 * @param {boolean} enforceRequired  (default false)
 * 
 * @returns {null|{errors:Array<string>, validate:()=>{valid:boolean, errors:Array<string>}}}
 */
export function initEmailValidator(formOrSelector, enforceRequired = false) {
    let form;

    if (typeof formOrSelector === 'string') {
        form = document.querySelector(formOrSelector);
    } else if (formOrSelector instanceof HTMLElement) {
        form = formOrSelector;
    }

    if (!form || !(form instanceof HTMLFormElement)) {
        console.warn('validateEmail.js: No form found for the given selector.');
        return null;
    }

    return createEmailValidator(form, enforceRequired);
}
