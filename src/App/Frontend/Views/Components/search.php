<?php

/**
 * Components/search.php
 *
 * This partial renders a search form and a small JavaScript snippet that
 * handles the reset button.  It is used by various list pages (e.g. items,
 * users, etc.) to provide a quick “search by name” capability.
 *
 * @var string $action    The URL to which the form will be submitted.
 * @var string $search    The current value that should be pre-filled in the
 *                        input field.
 * @var string $resetUrl  The URL to redirect to when the reset button is
 *                        pressed.
 */

/* ----------  Default-value handling --------------------------------------- */
// Ensure the variables are defined; fall back to empty strings if not provided.
$action   = $action ?? '';
$search   = $search ?? '';
$resetUrl = $resetUrl ?? '';
?>
<section>
    <!-- The search form uses the GET method so that the query string
        reflects the search term and can be bookmarked or shared. -->
    <form id="searchForm" action="<?= $action ?>" method="GET">
        <!-- Text field for the user to type the search term -->
        <div class="input-group">
            <input type="text"
                class="field"
                name="name"
                placeholder="Search by name…"
                value="<?= htmlspecialchars($search) ?>">
            <!-- Submit button - triggers a GET request to $action -->
            <button class="btn" type="submit">Search</button>
            <!-- Reset button - clears the input and triggers the listener below -->
            <button type="reset">Reset</button>
        </div>
    </form>

    <script>
        /*  When the user clicks the reset button, we want to:
            1. Clear the input field;
            2. Redirect the browser to the entity's home page ($resetUrl). */
        document.getElementById('searchForm').addEventListener('reset', function() {
            // The reset event clears the form fields automatically.
            // We wait until the native reset finishes before manipulating the DOM.
            setTimeout(() => {
                document.querySelector('input[name="name"]').value = '';
            }, 0);
            // After the field is cleared, redirect to the home route.
            setTimeout(() => {
                window.location.href = '<?= $resetUrl ?>';
            }, 0);
        });
    </script>

    <style>
        /* The flex-box container that holds the input and buttons. */
        .input-group {
            /* Use flex layout so children (input + buttons) line up horizontally. */
            display: flex;
            /* Vertically center all flex items (input and buttons). */
            align-items: center;
            /* Add a small horizontal space between items - 8 px is a decent default. */
            gap: 8px;
            /* Prevent the items from wrapping onto a new line, even on narrow screens. */
            flex-wrap: nowrap;
        }

        /* The text input field inside the group. */
        .input-group .field {
            /* Allow the input to grow and shrink as needed:
                flex-grow:   1 - takes up remaining space;
                flex-shrink: 1 - can shrink if the container is too small;
                flex-basis:  auto - starts at its natural size. */
            flex: 1 1 auto;
            /* Ensure the input can shrink below its content width without overflowing. */
            min-width: 0;
        }

        /* The button elements inside the group */
        .input-group .btn {
            /* Keep the button at its intrinsic width - it neither grows nor shrinks. */
            flex: 0 0 auto;
            /* Prevent the button text from wrapping onto a second line,
                ensuring the button stays a single line tall. */
            white-space: nowrap;
        }

        /* Responsive tweaks for very small viewports. */
        @media (max-width:350px) {
            /* Reduce spacing between items on narrow screens to save horizontal real-estate. */
            .input-group {
                gap: 6px;
            }

            /* Make the buttons a bit smaller and more touch-friendly on tiny devices. */
            .input-group .btn {
                /* Smaller padding = smaller clickable area. */
                padding: 6px 8px;
                /* Slightly smaller font for a better fit. */
                font-size: 14px;
            }
        }
    </style>
</section>