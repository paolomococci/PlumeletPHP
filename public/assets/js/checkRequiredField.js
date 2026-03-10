/**
 * checkRequiredField.js - Enhanced validator
 *
 * Features:
 *   1. Finds all `<input>`, `<textarea>` and `<select>` elements with the `required` attribute inside a form.
 *   2. Checks that each required element has a non-empty value (handles checkboxes, radios, files, dates, etc.).
 *   3. Handles radio groups - it is sufficient that **one** radio in the group is checked.
 *   4. Builds a readable list of errors (label - name/id).
 *   5. Exposes the list via `validator.errors` and, for backward compatibility, via `window.validationErrors`.
 *   6. Adds `aria-invalid="true"` to invalid elements to improve accessibility.
 *   7. Allows message customization (i18n) through the `messages` object.
 * 
 */

/** --------------------------------------------------------------------------
 * Global key used to expose validation errors to legacy code or other
 * scripts that might need to access the error list without interacting
 * with the validator instance.
 * --------------------------------------------------------------------------- */
const ERRORS_KEY = 'validationErrors';

// Configuration of error messages - can be overridden externally.
export const messages = {
    // Default message for a missing field.  The function receives the field's label
    // and returns a string that will be added to the errors array.
    missingField: (label) => `- ${label}`,
};

/**
 * Returns the text of the `<label>` associated with `el`, or falls back to
 * the element's `name`, `id`, or a generic string if neither is present.
 *
 * @param {HTMLElement} el
 * 
 * @returns {string}
 */
function getFieldLabel(el) {
    const id = el.id;
    // Look for a label that has a `for` attribute pointing to this id.
    if (id) {
        const label = document.querySelector(`label[for="${id}"]`);
        if (label) return label.textContent.trim();
    }
    // If no label, use the `name` attribute, or the id again, or a default.
    return el.name || id || 'Unnamed field';
}

/**
 * Determines whether a single element is “empty”.
 * Handles special cases for checkboxes, radios, files, dates, etc.
 *
 * @param {HTMLElement} el
 * 
 * @returns {boolean} true if the element has a valid value
 */
function hasValue(el) {
    const type = el.type;

    // For checkboxes we consider the field valid only if it is checked.
    if (type === 'checkbox') {
        return el.checked;
    }

    // Radio buttons are handled at the group level; return false here
    // so that individual radios are not validated separately.
    if (type === 'radio') {
        return false;
    }

    // A file input is valid if the user has selected at least one file.
    if (type === 'file') {
        return el.files && el.files.length > 0;
    }

    // For all other input types (text, email, number, date, etc.) we consider
    // the field valid only if its value is non-empty after trimming whitespace.
    return el.value != null && el.value.trim() !== '';
}

/**
 * Scans the form and returns an array of error messages 
 * for required fields that are missing.
 *
 * @param {HTMLFormElement} form
 * 
 * @returns {Array<string>}
 */
export function findMissingRequired(form) {
    // Cache the required elements to avoid re-querying on subsequent calls.
    if (!form._requiredEls) {
        form._requiredEls = Array.from(
            form.querySelectorAll(
                'input[required], textarea[required], select[required]'
            )
        );
    }

    // Array that will hold all error messages.
    const missing = [];

    // Helper object to group radio inputs by their `name` attribute.
    const radioGroups = {};

    // Iterate over every required element in the form.
    form._requiredEls.forEach((el) => {
        const type = el.type;

        // Radio button handling - postpone validation until after the loop.
        if (type === 'radio') {
            const name = el.name;
            if (!radioGroups[name]) radioGroups[name] = [];
            radioGroups[name].push(el);
            return;
        }

        // For non-radio elements, check if they have a valid value.
        if (!hasValue(el)) {
            // Build an error message using the label or fallback name.
            missing.push(messages.missingField(getFieldLabel(el)));
            // Mark the element as invalid for accessibility purposes.
            el.setAttribute('aria-invalid', 'true');
        } else {
            // If the element is valid, remove any previous aria-invalid flag.
            el.removeAttribute('aria-invalid');
        }
    });

    // Validate each radio group - a group is valid if at least one radio is checked.
    Object.values(radioGroups).forEach((group) => {
        const anyChecked = group.some((radio) => radio.checked);
        if (!anyChecked) {
            // Use the label of the first radio (usually the shared label).
            const label = getFieldLabel(group[0]);
            missing.push(messages.missingField(label));
            // Mark all radios in the group as invalid.
            group.forEach((radio) => radio.setAttribute('aria-invalid', 'true'));
        } else {
            // If the group is valid, remove any aria-invalid flags.
            group.forEach((radio) => radio.removeAttribute('aria-invalid'));
        }
    });

    return missing;
}

/**
 * Create a validator object for a form.
 *
 * @param {HTMLFormElement} form
 * 
 * @returns {{errors:Array<string>, validate:()=>{valid:boolean, errors:Array<string>}}}
 */
function createValidator(form) {
    const validator = {
        // Holds the most recent validation errors.
        errors: [],

        validate() {
            // Run the missing-required check
            const missing = findMissingRequired(form);
            // Store the errors on the validator instance.
            validator.errors = missing;
            // Also expose the errors on the global window object for legacy code.
            window[ERRORS_KEY] = missing;
            // Return a simple result object.
            return { valid: missing.length === 0, errors: missing };
        },
    };

    // Perform an initial validation immediately upon creation.
    validator.validate();
    return validator;
}

/**
 * Initializes the validator on a form identified by a selector or directly by an element.
 * Returns `null` if the form cannot be found.
 *
 * @param {string|HTMLElement} formOrSelector
 * 
 * @returns {object|null}
 */
export function initCheckRequiredField(formOrSelector) {
    let form;

    // Resolve the form either from a CSS selector string or from an HTMLElement.
    if (typeof formOrSelector === 'string') {
        form = document.querySelector(formOrSelector);
    } else if (formOrSelector instanceof HTMLElement) {
        form = formOrSelector;
    }

    // Guard against a missing or non-form element.
    if (!form || !(form instanceof HTMLFormElement)) {
        console.warn('checkRequiredField.js: No form found for the given selector.');
        return null;
    }

    // Return a validator instance for the found form.
    return createValidator(form);
}
