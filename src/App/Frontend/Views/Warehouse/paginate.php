<?php 
    /*  Retrieve the search query if it exists, otherwise fall back to the GET value.
    If neither is set, $search will be an empty string. */
    $search = $search ?? ($_GET['name'] ?? '');

    /*  Base URL for building pagination links that preserve the current search parameters. */
    $baseUrl = '/warehouse/search';

    /**
     * 
     * Set the page layout and pass the page title to the layout renderer.
     * 
     * Concatenate with modern syntax using match (PHP 8+).
     * 
     * The match control structure, introduced in PHP 8.0, 
     * represents a modern and more powerful alternative to switch. 
     * It's an expression, not a simple statement, 
     * which means it returns a value that can be assigned to a variable 
     * or used directly.
     */
    $this->layout('layout', ['title' => "Warehouse - " . match(true) {
        $search !== '' => "Search term \"$search\" and paginate", 
        default => 'Paginate'
    }]);

    /*  Extract pagination data that was passed from the controller. */
    $current = $pagination['current'];
    $perPage = $pagination['perPage'];
    $pages   = $pagination['pages'];
    $prev    = $pagination['prev'];
    $next    = $pagination['next'];
?>

<!-- search component -->
<?php
    /*  Insert the reusable search component, passing the form action, current
        search term and the URL to reset to when the form is cleared. */
    $this->insert('Components/search', [
    'action'   => '/warehouse/search',
    'search'   => $search,
    'resetUrl' => '/warehouses',
    ]);
?>

<section>
<?php if (!empty($warehouses)): ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <table>
            <!-- table header -->
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <!-- table body -->
            <tbody>
            <?php foreach ($warehouses as $warehouse): ?>
                <tr>
                    <td><?= $this->e($warehouse->getId() ?? '') ?></td>
                    <td><a href="/warehouse/<?= $this->e($warehouse->getId() ?? '') ?>">
                        <?= $this->e($warehouse->getName() ?? '') ?>
                    </a></td>
                    <td><?= $this->e((string) $warehouse->getAddress() ?? '') ?></td>
                    <td class="optional"><?= $this->e((string) $warehouse->getEmail() ?? '') ?></td>
                    <td><?= $this->e($warehouse->getType() ?? '') ?></td>
                    <td><a href="/warehouse/update/<?= $this->e($warehouse->getId()) ?? '' ?>">edit</a></td>
                    <td><a href="/warehouse/delete/<?= $this->e($warehouse->getId()) ?? '' ?>">del</a></td>
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

        <p><a href="/warehouse/new">add new warehouse</a></p>
    </section>
<?php else: ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <h5>No results found!</h5>
        <p><a href="/warehouse/new">add new warehouse</a></p>
    </section>
<?php endif; ?>
</section>