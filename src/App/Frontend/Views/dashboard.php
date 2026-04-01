<?php
/* app/views/auth.php - a lightweight layout for the auth stack */
?>
<!DOCTYPE html>
<!-- The root document declaration for HTML5. -->
<html lang="en">

<head>
    <!-- Standard meta tags for character set and responsive viewport. -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?= $this->e($title) ?? 'Auth' ?></title>

    <!-- External stylesheet reference -->
    <link rel="stylesheet" href="/assets/css/simple.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<!-- Main wrapper that extends full width (used for header background). -->

<body id="app" class="full-bleed">
    <!-- 1) Header - visible only on larger viewports due to .optional -->
    <header class="app-header" role="banner">
        <h6 class="logged-operator">
            <?= $this->e($_SESSION['operator']?->getEmail() ?? 'guest') ?>
            <em class="operator-role">(<?= $this->e($_SESSION['operator']?->getRole() ?? '') ?>)</em>
        </h6>
        <!-- Page title rendered inside the header. -->
        <h6><span class="view-title"><?= $this->e($title) ?></span></h6>
    </header>


    <div class="container">
        <!-- 2) Navigation -->
        <?php
        // Extract the current request path for link state comparison.
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        ?>
        <nav role="navigation" aria-label="Main menu">
            <ul class="horizontal-nav">
                <li>
                    <!-- The 'dashboard' link becomes disabled if the current path contains '/dashboard' -->
                    <a href="/dashboard"
                        <?= (str_contains($path, '/dashboard') ? 'class="disabled" aria-disabled="true"' : '') ?>>dashboard</a>
                </li>
                <li>
                    <!-- Disable the 'items' link when on any '/item' route -->
                    <a href="/items"
                        <?= (str_contains($path, '/item') ? 'class="disabled" aria-disabled="true"' : '') ?>>items</a>
                </li>
                <li>
                    <!-- Disable the 'users' link when on any '/user' route -->
                    <a href="/users"
                        <?= (str_contains($path, '/user') ? 'class="disabled" aria-disabled="true"' : '') ?>>users</a>
                </li>
                <li>
                    <!-- Disable the 'warehouses' link when on any '/warehouse' route -->
                    <a href="/warehouses"
                        <?= (str_contains($path, '/warehouse') ? 'class="disabled" aria-disabled="true"' : '') ?>>warehouses</a>
                </li>
                <li>
                    <!-- logout -->
                    <a href="/logout">logout</a>
                </li>
            </ul>
        </nav>
        <!-- 3) Main content area - the view-specific content is injected here. -->
        <main role="main">
            <!-- Render the content section defined in the child template. -->
            <?= $this->section("content") ?>
        </main>
    </div>
</body>

</html>