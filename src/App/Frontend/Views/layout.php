<!DOCTYPE html>
<!-- The root document declaration for HTML5. -->
<html lang="en">

<head>
    <!-- Standard meta tags for character set and responsive viewport. -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Page title - escaped to avoid XSS. -->
    <title><?= $this->e($title) ?></title>

    <!-- External stylesheet reference -->
    <link rel="stylesheet" href="/assets/css/simple.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<body>
    <!-- Main wrapper that extends full width (used for header background). -->
    <div id="app" class="full-bleed">
        <!-- 1) Header - visible only on larger viewports due to .optional -->
        <header class="app-header optional" role="banner">
            <!-- Page title rendered inside the header. -->
            <span><?= $this->e($title) ?></span>
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
                        <!-- The 'home' link becomes disabled if the current path contains '/home' -->
                        <a href="/home" 
                            <?= (str_contains($path, '/home') ? 'class="disabled" aria-disabled="true"' : '') ?>
                            >home</a>
                    </li>
                    <li>
                        <!-- Disable the 'items' link when on any '/item' route -->
                        <a href="/items" 
                            <?= (str_contains($path, '/item') ? 'class="disabled" aria-disabled="true"' : '') ?>
                            >items</a>
                    </li>
                    <li>
                        <!-- Disable the 'users' link when on any '/user' route -->
                        <a href="/users" 
                            <?= (str_contains($path, '/user') ? 'class="disabled" aria-disabled="true"' : '') ?>
                            >users</a>
                    </li>
                    <li>
                        <!-- Disable the 'warehouses' link when on any '/warehouse' route -->
                        <a href="/warehouses" 
                            <?= (str_contains($path, '/warehouse') ? 'class="disabled" aria-disabled="true"' : '') ?>
                            >warehouses</a>
                    </li>
                </ul>
            </nav>

            <!-- 3) Main content area - the view-specific content is injected here. -->
            <main role="main">
                <!-- Render the content section defined in the child template. -->
                <?= $this->section("content") ?>
            </main>
        </div>
    </div>

</body>

</html>