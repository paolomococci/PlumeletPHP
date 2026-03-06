<?php
$alertMsg = $alertMsg ?? 'This action may be irreversible.';
?>

<div
    id="confirmComponent"
    class="modal"
    aria-hidden="true"
    role="dialog"
    aria-labelledby="modalTitle"
    aria-modal="true">
    <div class="modal-backdrop"></div>
    <div class="modal-panel">
        <h5 id="modalTitle">Are you sure you want to proceed?</h5>
        <!-- Displays whatever string is stored in $alertMsg. -->
        <p><?= $this->e($alertMsg); ?></p>
        <div class="modal-actions">
            <button id="cancelBtn" class="component-btn" type="button">Cancel</button>
            <button id="confirmBtn" class="component-btn-apply" type="button">Apply</button>
        </div>
    </div>
</div>

<style>
    /* The modal is hidden by default (display:none). */
    .modal {
        display: none;
        position: fixed;
        /* inset:0, shorthand for top:0; right:0; bottom:0; left:0, makes it fill the viewport. */
        inset: 0;
        /* z-index:1000 places it on top of most content. */
        z-index: 1000;
    }

    /* When aria-hidden="false" the CSS selector flips it to display:block, making the modal visible. */
    .modal[aria-hidden="false"] {
        display: block;
    }

    /* The backdrop sits behind the panel but still inside the .modal container. */
    .modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
    }

    /* The panel is styled dark-themed, rounded, and has a drop-shadow. */
    .modal-panel {
        position: relative;
        /* margin: 6vh auto vertically centres the panel 6 vh from the top, horizontally centered. */
        margin: 6vh auto;
        /**
         * Responsive:
         * 
         * 18rem, the minimum width the element can shrink to;
         * 90vw, the preferred width, calculated as 90 % of the viewport’s width;
         * 38rem, the maximum width an element can have.
         * 
         * clamp() lets the modal adapt fluidly across devices while staying within sensible size limits.
         * 
         */
        width:clamp(18rem, 90vw, 38rem);
        background: #333;
        padding: 1.25rem;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    /* Flexbox makes the two buttons line up on the right. */
    .modal-actions {
        display: flex;
        /* gap gives a small space between them. */
        gap: 0.5rem;
        justify-content: flex-end;
        margin-top: 1rem;
    }

    /* Basic button styling – neutral, slightly rounded. */
    .component-btn {
        padding: 0.5rem 0.8rem;
        border-radius: 6px;
        border: 1px solid #bbb;
        background: #eee;
    }
</style>

<script>
    /* Grab the primary UI elements that we’ll interact with. */

    // The button that opens the modal.
    const postBtn = document.getElementById("postBtn");
    // The modal container itself.
    const modal = document.getElementById("confirmComponent");
    // Cancel button inside the modal.
    const cancelBtn = document.getElementById("cancelBtn");
    // Confirm button inside the modal.
    const confirmBtn = document.getElementById("confirmBtn");
    // The form that will be submitted.
    const postForm = document.getElementById("postForm");

    /* Variables that will help us manage focus when the modal is open. */
    // The element that had focus before the modal opened.
    let lastFocused = null;
    // All focusable elements inside the modal.
    let focusable = [];
    // The first focusable element, used for tabbing.
    let firstFocusable = null;
    // The last focusable element, used for tabbing.
    let lastFocusable = null;

    /* Modal lifecycle helpers. */

    /**
     * Open the modal and set up focus trapping.
     */
    function openModal() {
        // Remember the element that was focused before we opened the modal.
        lastFocused = document.activeElement;
        // Make the modal visible to assistive tech.
        modal.setAttribute("aria-hidden", "false");
        // Prevent background scrolling while the modal is open.
        document.body.style.overflow = "hidden";
        // Find all focusable elements inside the modal and bind the trap handler.
        trapFocus(modal);
        // Put keyboard focus on the confirm button so the user can start interacting immediately
        confirmBtn.focus();
    }

    /**
     * Close the modal and restore focus to the original element.
     */
    function closeModal() {
        // Hide the modal from assistive tech.
        modal.setAttribute("aria-hidden", "true");
        // Re-enable scrolling on the main document.
        document.body.style.overflow = "";
        // Remove the keydown listener that was trapping focus.
        releaseFocusTrap();
        // Return focus to whatever was active before we opened the modal.
        if (lastFocused) lastFocused.focus();
    }

    /* Focus-trapping helpers */

    /**
     * Build a list of all elements that can receive focus inside a container
     */
    function trapFocus(container) {
        focusable = Array.from(
            // Grab all the standard focusable elements.
            container.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
            ),
        ).filter(
            // Exclude disabled or aria-hidden elements.
            (el) => !el.hasAttribute("disabled") && el.getAttribute("aria-hidden") !== "true",
        );
        // Cache the first & last elements to implement wrap-around tabbing.
        firstFocusable = focusable[0];
        lastFocusable = focusable[focusable.length - 1];
        // Listen for key presses to keep the focus inside the modal.
        document.addEventListener("keydown", handleKeydown);
    }

    /**
     * Remove the keydown listener when the modal is closed.
     */
    function releaseFocusTrap() {
        document.removeEventListener("keydown", handleKeydown);
    }

    /**
     * Handle key events while the modal is open.
     */
    function handleKeydown(e) {
        // ESC key, closes the modal.
        if (e.key === "Escape") {
            closeModal();
        }
        // TAB key, keep focus cycling within the modal.
        if (e.key === "Tab") {
            // If there are no focusable elements, nothing to do.
            if (focusable.length === 0) {
                e.preventDefault();
                return;
            }
            // If Shift+Tab is pressed and the current element is the first one, jump to the last element.
            if (e.shiftKey && document.activeElement === firstFocusable) {
                e.preventDefault();
                lastFocusable.focus();
                // If Tab (no shift) is pressed on the last element, jump back to the first element.
            } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                e.preventDefault();
                firstFocusable.focus();
            }
        }
    }

    /* Event listeners */

    /**
     * Clicking the Post button opens the modal.
     */
    postBtn.addEventListener("click", (e) => {
        e.preventDefault();
        openModal();
    });

    /**
     * Clicking the Cancel button closes the modal.
     */
    cancelBtn.addEventListener("click", (e) => {
        e.preventDefault();
        closeModal();
    });

    /**
     * Clicking the Confirm button submits the form that lives behind the modal.
     */
    confirmBtn.addEventListener("click", (e) => {
        e.preventDefault();
        // Submit the POST form.
        postForm.submit();
    });

    // Clicking anywhere on the backdrop, outside the modal content, also closes the modal.
    modal.querySelector(".modal-backdrop").addEventListener("click", closeModal);
</script>