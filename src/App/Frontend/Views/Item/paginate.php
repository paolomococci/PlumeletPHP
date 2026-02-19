<?php
    use App\Backend\Models\Item;

    /*  Set the page layout and pass the page title to the layout renderer. */
    $this->layout('layout', ['title' => 'Item - Paginate']);

    /*  Extract pagination data that was passed from the controller. */
    $current = $pagination['current'];
    $perPage = $pagination['perPage'];
    $pages   = $pagination['pages'];
    $prev    = $pagination['prev'];
    $next    = $pagination['next'];

    /*  Retrieve the search query if it exists, otherwise fall back to the GET value.
    If neither is set, $search will be an empty string. */
    $search = $search ?? ($_GET['name'] ?? '');

    /*  Base URL for building pagination links that preserve the current search parameters. */
    $baseUrl = '/item/search';
?>

<!-- search component -->
<?php
    /*  Insert the reusable search component, passing the form action, current
        search term and the URL to reset to when the form is cleared. */
    $this->insert('Components/search', [
    'action'   => '/item/search',
    'search'   => $search,
    'resetUrl' => '/items',
    ]);
?>

<!-- item list -->
<section>
<?php if (! empty($items)): /* If there are items to display. */ ?>
    <section>
        <!--  Header for the list - shown only on larger viewports via the "optional" CSS class -->
        <h3 class="optional"><?= $this->e($view_title) ?></h3>
        <hr class="optional">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Description</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <!-- Link to the detailed view of the item. -->
                    <td>
                        <a href="/item/<?= $this->e($item->getId() ?? '') ?>">
                            <?= $this->e($item->getName() ?? '') ?>
                        </a>
                    </td>
                    <!-- Item price (converted to string for consistency). -->
                    <td><?= $this->e((string)$item->getPrice() ?? '') ?></td>
                    <!-- Item currency (converted to string for consistency). -->
                    <td><?= $this->e((string)$item->getCurrency() ?? '') ?></td>
                    <!-- Item description, shortened with the ellipsis helper. -->
                    <td><?= $this->e(Item::ellipsisPreserveWords(description: $item->getDescription(), limit: 32) ?? '') ?></td>
                    <!-- Edit link -->
                    <td><a href="/item/update/<?= $this->e($item->getId()) ?? '' ?>">edit</a></td>
                    <!-- Delete link -->
                    <td><a href="/item/delete/<?= $this->e($item->getId()) ?? '' ?>">del</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- pagination component -->
        <?php
            /*  Insert the reusable pagination component, supplying all the
                pagination helpers and preserving the search term in the query string. */
            $this->insert('Components/pagination', [
                'prev'    => $prev,
                'next'    => $next,
                'current' => $current,
                'pages'   => $pages,
                'baseUrl' => $baseUrl,
                'search'  => $search,
                'perPage' => $perPage,
            ]);
        ?>

        <!-- Link to create a new item. -->
        <p><a href="/item/new">add new item</a></p>
    </section>
<?php else: /* No items were found for the current search query */ ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr class="optional">
        <h5>No results found!</h5>
        <p><a href="/item/new">add new item</a></p>
    </section>
<?php endif; ?>
</section>