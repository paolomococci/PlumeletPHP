/**
 * validatePassword.js
 *
 * Validates all <input type="password"> elements inside a form.
 * Features:
 *   • Default strict rules (min length, upper/lower/digit/special) with
 *     configurable thresholds.
 *   • Optional `customRegex` that, when supplied, overrides the default
 *     rule set.
 *   • Caches the list of password inputs after the first run.
 *   • Adds `aria-invalid="true"` to invalid fields (accessibility).
 *   • Exposes errors globally via `window.passwordValidationErrors`.
 *
 * Usage:
 *   import { initPasswordValidator } from '/assets/js/validatePassword.js';
 *   const validator = initPasswordValidator('#postForm', { minLength: 12 });
 *   const { valid, errors } = validator.validate();
 */

/** --------------------------------------------------------------------------
 * Global key used to expose validation errors to legacy code or other
 * scripts that might need to access the error list without interacting
 * with the validator instance.
 * --------------------------------------------------------------------------- */
const GLOBAL_KEY = 'passwordValidationErrors';

/** --------------------------------------------------------------------
 * English messages (editable here if needed)
 * -------------------------------------------------------------------- */
const messages = {
    // Message when a required field is empty.
    requiredEmpty: (desc) => `Field "${desc}" is required but empty.`,
    // Message for the minimum length requirement.
    minLength: (desc, min, actual) =>
        `Field "${desc}" must be at least ${min} characters long (has ${actual}).`,
    // Message for missing uppercase letter.
    upperCase: (desc) => `Field "${desc}" must contain an uppercase letter.`,
    // Message for missing lowercase letter.
    lowerCase: (desc) => `Field "${desc}" must contain a lowercase letter.`,
    // Message for missing numeric digit.
    digit: (desc) => `Field "${desc}" must contain a digit.`,
    // Message for missing special (non-alphanumeric) character.
    special: (desc) => `Field "${desc}" must contain a special character.`,
    // Message when a supplied custom pattern is not matched.
    customPattern: (desc) => `Field "${desc}" does not match the required pattern.`,
};

/**
 * Helper: Return a human-readable description for an input element.
 * 
 * - First tries to find an <label> that points to the input via the
 *   `for` attribute. If that exists, we use the label's trimmed text
 *   content - this is usually the most user-friendly name.
 * - If no label is found, we fall back to the `name` attribute, then the
 *   `id`. If all of those are missing we return the literal string
 *   'Unnamed field' to avoid returning an empty string to the caller.
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
 * Validate a single password input.
 *
 * The function works in three distinct stages:
 *   1. Performs a required-field check if the input has `required`
 *       attribute set. Empty values (after trimming whitespace) trigger
 *       a single error message and an early return - no further checks
 *       are necessary because the field is already invalid.
 *
 *   2. If a `customRegex` is provided, the default rule set is bypassed.
 *       The regex is tested exclusively against the entered password.
 *       If it fails we push a generic pattern-mismatch message.
 *
 *   3. When no custom regex is supplied, we apply the default rule set
 *       which is configurable via the `options` object:
 *       • Minimum length.
 *       • At least one uppercase letter (if `requireUpper`).
 *       • At least one lowercase letter (if `requireLower`).
 *       • At least one digit (if `requireDigit`).
 *       • At least one special character (if `requireSpecial`).
 *
 * The function finally returns an object that signals overall validity
 * and carries any collected error messages.
 * 
 * @param {HTMLInputElement} el
 * @param {Object} options
 * 
 * @returns {{valid:boolean, messages:Array<string>}}
 */
function validateSinglePassword(el, options) {
    const desc = getFieldDescription(el);
    const msgs = [];

    const pwd = el.value || '';

    // 1. Required check.
    if (el.required && pwd.trim() === '') {
        msgs.push(messages.requiredEmpty(desc));
        return { valid: false, messages: msgs };
    }

    // 2. If a custom regex is supplied, use it exclusively.
    if (options.customRegex) {
        if (pwd && !options.customRegex.test(pwd)) {
            msgs.push(messages.customPattern(desc));
        }
        return { valid: msgs.length === 0, messages: msgs };
    }

    // 3. Default rule set.
    if (pwd.length < options.minLength) {
        msgs.push(messages.minLength(desc, options.minLength, pwd.length));
    }
    if (options.requireUpper && !/[A-Z]/.test(pwd)) {
        msgs.push(messages.upperCase(desc));
    }
    if (options.requireLower && !/[a-z]/.test(pwd)) {
        msgs.push(messages.lowerCase(desc));
    }
    if (options.requireDigit && !/[0-9]/.test(pwd)) {
        msgs.push(messages.digit(desc));
    }
    if (options.requireSpecial && !/[^A-Za-z0-9]/.test(pwd)) {
        msgs.push(messages.special(desc));
    }

    return { valid: msgs.length === 0, messages: msgs };
}

/**
 * Find all password inputs in a form, validate each, and return an array
 * of error strings. The list of inputs is cached on the form (`_pwdInputs`).
 *
 * Key points:
 *   - We guard against misuse by checking that the passed element is
 *     an actual HTMLFormElement; if not, we log a warning and bail out.
 *
 *   - On first invocation we create a private property `_pwdInputs` on the
 *     form object and store an array of all password inputs found inside
 *     the form. This avoids re-querying the DOM on subsequent validations
 *     and therefore improves performance for large forms or repeated
 *     validations (e.g., on every form field change).
 *
 *   - We iterate over each cached password input, run the
 *     `validateSinglePassword` helper, and aggregate any error messages.
 *
 *   - For each field we also set the accessibility attribute `aria-invalid`
 *     when the field is invalid, and we remove that attribute when the
 *     field passes validation. This is vital for screen-reader users
 *     to be notified of input errors.
 *
 *   - Finally, we return the collected errors - the consumer can decide
 *     what to do with them (display inline, console log, etc.).
 *
 * @param {HTMLFormElement} form
 * @param {Object} options
 * 
 * @returns {Array<string>}
 */
export function findPasswordErrors(form, options) {
    if (!(form instanceof HTMLFormElement)) {
        console.warn('validatePassword.js: Provided element is not a form.');
        return [];
    }

    // Cache the inputs after the first call.
    if (!form._pwdInputs) {
        form._pwdInputs = Array.from(form.querySelectorAll('input[type="password"]'));
    }

    const errors = [];

    form._pwdInputs.forEach((el) => {
        const { valid, messages } = validateSinglePassword(el, options);
        if (!valid) {
            errors.push(...messages);
            el.setAttribute('aria-invalid', 'true');
        } else {
            el.removeAttribute('aria-invalid');
        }
    });

    return errors;
}

/**
 * Create a validator object that holds the current error list
 * and mirrors it to the global namespace.
 *
 * The validator returned by this factory exposes two public members:
 *   - `errors`: a mutable array that always holds the *latest* validation
 *     error list for this particular form instance.
 *   - `validate()`: a method you call whenever you want to re-run the
 *     validation logic (e.g., on form submission). It re-computes the
 *     errors, updates the internal state, syncs with the global
 *     `window.passwordValidationErrors` key, and returns an object
 *     indicating overall validity and the current error list.
 *
 * The factory also performs an initial validation immediately after
 * construction so the consumer has an up-to-date error state without
 * needing to call `validate()` manually right away.
 *
 * @param {HTMLFormElement} form
 * @param {Object} options
 * 
 * @returns {{errors:Array<string>, validate:()=>{valid:boolean, errors:Array<string>}}}
 */
function createPasswordValidator(form, options) {
    const validator = {
        errors: [],

        validate() {
            const errors = findPasswordErrors(form, options);
            validator.errors = errors;
            window[GLOBAL_KEY] = errors; // global fallback
            return { valid: errors.length === 0, errors };
        },
    };

    // Initial validation so the state is ready immediately.
    validator.validate();
    return validator;
}

/**
 * Public wrapper - accepts a selector or an element and optional rule overrides.
 *
 * This helper is the entry point that callers use to set up a password
 * validator on a form.  It does two things:
 *
 *   1. Normalizes the *formOrSelector* argument so that we end up with an
 *      actual `<form>` element.  The argument can be either a CSS selector
 *      string or a reference to an HTMLElement.
 *
 *   2. Merges any supplied rule overrides with the library defaults.
 *
 * Once those two steps are done the function delegates to
 * `createPasswordValidator`, which returns the validator instance.
 *
 * @param {string|HTMLElement} formOrSelector
 *   - A CSS selector string that selects a `<form>` element,
 *     or a direct reference to a form element.
 *
 * @param {Object} opts - rule overrides
 *   - Optional object that can contain any of the validation options
 *     defined in `DEFAULTS`.  It is used to customize the validator.
 *
 * @returns {null|{errors:Array<string>, validate:()=>{valid:boolean, errors:Array<string>}}}
 *   - If a form could be found and is a real `<form>` element, an object
 *     with a `validate()` method is returned.  Otherwise `null` is
 *     returned and a warning is printed to the console.
 */
export function initPasswordValidator(formOrSelector, opts = {}) {
    /** 
     * 1. Default rule configuration, these are the rules the
     * validator will enforce unless overridden by `opts`.
     */
    const DEFAULTS = {
        minLength: 10,          // Minimum password length
        requireUpper: true,     // At least one uppercase letter
        requireLower: true,     // At least one lowercase letter
        requireDigit: true,     // At least one numeric digit
        requireSpecial: true,   // At least one special character
        customRegex: null,      // Optional user-supplied RegExp
    };

    /**
     * 2. Merge defaults with any overrides supplied by the caller.
     * The spread operator (`{...DEFAULTS, ...opts}`) creates a brand-new
     * object so the original DEFAULTS remain immutable.
     * 
     */
    const options = { ...DEFAULTS, ...opts };

    /**
     * 3. Resolve the form reference. 
     * The caller may have passed:
     * - a CSS selector string, use document.querySelector()
     * - an actual HTMLElement, use it directly
     * 
     */
    let form;
    if (typeof formOrSelector === 'string') {
        form = document.querySelector(formOrSelector);
    } else if (formOrSelector instanceof HTMLElement) {
        form = formOrSelector;
    }

    /**
     * 4. Validate that we actually have a form element.
     * If not, this warn the developer and return `null` so the caller can 
     * handle the error gracefully.
     * 
     */
    if (!form || !(form instanceof HTMLFormElement)) {
        console.warn('validatePassword.js: No form found for the given selector.');
        return null;
    }

    /**
     * 5. All good, delegate to the private factory that creates the 
     * validator instance. This function returns the public API 
     * (`validate()` plus the `errors` array).
     * 
     */
    return createPasswordValidator(form, options);
}
