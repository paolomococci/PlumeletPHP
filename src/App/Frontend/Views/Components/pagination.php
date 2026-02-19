<?php

/**
 * Components/pagination.php
 *
 * This partial renders a pagination control that keeps the current
 * search term (`$search`) and number-per-page (`$perPage`) intact
 * while moving between pages.
 *
 * @var string $prev       URL-segment for the previous page (or null if none).
 * @var string $next       URL-segment for the next page (or null if none).
 * @var string $current    Current page number (1-based).
 * @var string $pages      Total number of pages (integer, as string).
 * @var string $baseUrl    Base path for the pagination links (e.g. "/item/search").
 * @var string $searchParams  Associative array that will always be merged into
 *                            the query string of every page link.
 */

/* ----------  Default-value handling --------------------- */
// If `$prev` is not supplied, treat it as “no previous page”.
$prev    = $prev ?? null;
// Likewise for the next page.
$next    = $next ?? null;
// Current page number (empty string if unknown).
$current = $current ?? '';
// Total number of pages.
$pages   = $pages ?? '';
// The base endpoint (e.g. "/item/search").
$baseUrl = $baseUrl ?? '';
// Current search filter.
$search  = $search ?? '';
// Current “items per page” setting.
$perPage = $perPage ?? '';

/*  Parameters that must always be present in the pagination links.
    These are merged with the specific page number later. */
$searchParams = ['name' => $search, 'perPage' => $perPage];

/*  Helper function that constructs a link to a specific page while
    keeping the current search and perPage values. */
function linkPage($page, $baseUrl, $searchParams)
{
    /* Merge the persistent parameters with the requested page number. */
    $query = http_build_query(array_merge($searchParams, ['page' => $page]));
    /* Concatenate the base URL with the query string. */
    return $baseUrl . '?' . $query;
}
?>

<nav class="pagination">
    <ul class="horizontal-list">
        <?php if ($prev !== null): ?>
            <!-- Render a “Prev” link only if a previous page exists. -->
            <li><a href="<?= $this->e(linkPage($prev, $baseUrl, $searchParams)) ?>">Prev</a></li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <!-- Each numbered link points to its own page but keeps `$search`
                and `$perPage` from `$searchParams`. -->
            <li>
                <!-- Apply a “disabled” class when the link refers to the
                    current page. This prevents clicking on the current
                    page number. -->
                <a href="<?= $this->e(linkPage($i, $baseUrl, $searchParams)) ?>"
                    class="<?= ($i === $current) ? 'disabled' : '' ?>"
                    aria-disabled="<?= ($i === $current) ? 'true' : 'false' ?>">
                    <!-- ARIA attribute communicates the same state to assistive technologies. -->
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($next !== null): ?>
            <!-- Render a “Next” link only if a next page exists. -->
            <li><a href="<?= $this->e(linkPage($next, $baseUrl, $searchParams)) ?>">Next</a></li>
        <?php endif; ?>
    </ul>
</nav>

<style>
    /* Layout of the pagination list - horizontal, evenly spaced. */
    .horizontal-list {
        /* Flex container so all <li> children sit side-by-side. */
        display: flex;
        /* Vertically align the items in the centre. */
        align-items: center;
        /* Distribute remaining space between the first and last item. */
        justify-content: space-between;
        /* Remove default list styling so the list appears “inline”. */
        list-style: none;
        /* Reset padding/margin to avoid unwanted gaps. */
        padding: 0;
        margin: 0;
    }

    /* Individual list items - simple right-margin for spacing. */
    .horizontal-list li {
        margin-right: 20px;
    }

    /* Styling for disabled page links. */
    a.disabled {
        /* Block pointer events so the link cannot be activated. */
        pointer-events: none;
        /* Visually indicate inactivity. */
        opacity: 0.5;
        color: #999;
        /* No underline - keeps the control looking like plain text. */
        text-decoration: none;
        /* Cursor shows “not-allowed” to signal non-clickable. */
        cursor: not-allowed;
    }
</style>