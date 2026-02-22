<?php

/**
 * If $search is already set, keep its value. Otherwise, set it to the escaped value of the 'name' GET parameter.
 * The operator `??=` (null‑coalescing assignment) means: $search = $search ?? <expression>;
 * 
 * Retrieve the raw 'name' query string safely: 
 * filter_input(INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS)
 * - INPUT_GET: looks into the $_GET super-global;
 * - `name`: the key we expect;
 * - FILTER_SANITIZE_SPECIAL_CHARS: strips or encodes special HTML characters 
 *   (`<`, `>`, `"`, `&`, etc.) to prevent XSS.
 * If the key doesn't exist, filter_input returns NULL.
 * 
 * If the filter returns NULL (i.e., no 'name' parameter), 
 * we fall back to an empty string via the inner `?? ''`.
 * 
 * The resulting string is then passed to `$this->e()`, which is 
 * typically an escaping helper (e.g., htmlspecialchars).
 * 
 * Finally, `$search` holds a safe, sanitized string that can be used 
 * for display, querying, etc., without risk of undefined index warnings.
 */
$search ??= $this->e(
    filter_input(INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? ''
);

/*  Base URL for building pagination links that preserve the current search parameters. */
$baseUrl = '/user/search';

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
$this->layout('layout', ['title' => "User - " . match (true) {
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
    'action'   => '/user/search',
    'search'   => $search,
    'resetUrl' => '/users',
]);
?>

<section>
    <?php if (!empty($users)): ?>
        <section>
            <h3><?= $this->e($view_title) ?></h3>
            <hr>
            <table>
                <!-- table header -->
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <!-- table body -->
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $this->e($user->getId() ?? '') ?></td>
                            <td><a href="/user/<?= $this->e($user->getId() ?? '') ?>">
                                    <?= $this->e($user->getName() ?? '') ?>
                                </a></td>
                            <td><a href="/user/update/<?= $this->e($user->getId()) ?? '' ?>">edit</a></td>
                            <td><a href="/user/delete/<?= $this->e($user->getId()) ?? '' ?>">del</a></td>
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

            <p><a href="/user/new">add new user</a></p>
        </section>
    <?php else: ?>
        <section>
            <h3><?= $this->e($view_title) ?></h3>
            <hr>
            <h5>No results found!</h5>
            <p><a href="/user/new">add new user</a></p>
        </section>
    <?php endif; ?>
</section>