<?php
    /*  Set the page layout and pass the page title to the layout renderer */
    $this->layout('layout', ['title' => 'Item - Paginate']);

    /*  Extract pagination data that was passed from the controller */
    $current = $pagination['current'];
    $perPage = $pagination['perPage'];
    $pages   = $pagination['pages'];
    $prev    = $pagination['prev'];
    $next    = $pagination['next'];

    /*  Retrieve the search query if it exists, otherwise fall back to the GET value.
    If neither is set, $search will be an empty string. */
    $search = $search ?? ($_GET['name'] ?? '');

    /*  Base URL for building pagination links that preserve the current search
    parameters. */
    $baseUrl = '/item/search';

    /*  Parameters that must always be present in the pagination links.
    These are merged with the specific page number later. */
    $searchParams = ['name' => $search, 'perPage' => $perPage];

    /*  Helper function that constructs a link to a specific page while
    keeping the current search and perPage values. */
    function linkPage($page, $baseUrl, $searchParams)
    {
    // Build query string: /item/search?name=xxx&perPage=5&page=n
    $query = http_build_query(array_merge($searchParams, ['page' => $page]));
    return $baseUrl . '?' . $query;
    }
?>

<!-- search form -->
<section>
    <form id="searchForm" action="/item/search" method="GET">
        <div class="input-group">
            <input type="text"
                name="name"
                placeholder="Search by name…"
                value="<?= $this->e($search ?? '') ?>">
            <button type="submit">Search</button>
            <button type="reset">Reset</button>
        </div>
    </form>

    <script>
        /*  When the user clicks the reset button, we want to:
            1. Clear the input field.
            2. Redirect the browser to the entity's home page (/items). */
        document.getElementById('searchForm').addEventListener('reset', function (e) {
            // After reset, set the input to an empty string.
            setTimeout(() => document.querySelector('input[name="name"]').value = '', 0);
            // Redirect to the home route for this entity.
            setTimeout(() => { window.location.href = '/items'; }, 0);
        });
    </script>
</section>

<!-- item list -->
<section>
<?php if (! empty($items)): ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <!-- Display the item ID -->
                    <td><?= $this->e($item->getId() ?? '') ?></td>
                    <!-- Link to the detailed view of the item -->
                    <td>
                        <a href="/item/<?= $this->e($item->getId() ?? '') ?>">
                            <?= $this->e($item->getName() ?? '') ?>
                        </a>
                    </td>
                    <!-- Item price (converted to string for consistency) -->
                    <td><?= $this->e((string)$item->getPrice() ?? '') ?></td>
                    <!-- Item currency (converted to string for consistency) -->
                    <td><?= $this->e((string)$item->getCurrency() ?? '') ?></td>
                    <!-- Item description -->
                    <td><?= $this->e($item->getDescription() ?? '') ?></td>
                    <!-- Creation date formatted as dd/mm/YYYY at HH:mm -->
                    <td><?= $this->e($item->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                    <!-- Last update date formatted as day‑month‑year at HH:mm:ss -->
                    <td><?= $this->e($item->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                    <!-- Edit link -->
                    <td><a href="/item/update/<?= $this->e($item->getId()) ?? '' ?>">edit</a></td>
                    <!-- Delete link -->
                    <td><a href="/item/delete/<?= $this->e($item->getId()) ?? '' ?>">del</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- pagination navigation bar -->
        <nav class="pagination">
            <ul>
                <?php if ($prev !== null): ?>
                    <li><a href="<?= htmlspecialchars(linkPage($prev, $baseUrl, $searchParams)) ?>">Prev</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <!-- Highlight the current page with the 'active' class -->
                    <li class="<?= ($i === $current) ? 'active' : '' ?>">
                    <a href="<?= htmlspecialchars(linkPage($i, $baseUrl, $searchParams)) ?>">
                        <?= $i ?>
                    </a>
                    </li>
                <?php endfor; ?>

                <?php if ($next !== null): ?>
                    <li><a href="<?= htmlspecialchars(linkPage($next, $baseUrl, $searchParams)) ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <p><a href="/item/new">add new item</a></p>
    </section>
<?php else: /* No items were found for the current search query */?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <h5>No results found!</h5>
        <p><a href="/item/new">add new item</a></p>
    </section>
<?php endif; ?>
</section>