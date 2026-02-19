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

    <!-- Inline CSS block with custom properties and responsive styles. -->
    <style>
        /* Root variables for spacing and element heights. */
        :root {
            --gap: 12px;
            --height: 56px;
        }

        /* Box‑sizing reset for all elements. */
        * {
            box-sizing: border-box
        }

        /* Base font size for the body. */
        body {
            font-size: 0.95rem;
        }

        /* Padding for the navigation container. */
        nav {
            padding: 0.5rem;
        }

        /* Utility class to stretch an element to full viewport width. */
        .full-bleed {
            width: 100vw;
            margin-left: 50%;
            transform: translateX(-50%);
        }

        /* Utility class that can be toggled for optional elements. */
        .optional {
            display: block;
        }

        /* Header layout using flexbox - the main app banner. */
        .app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--gap);
            padding: 8px;
            height: var(--height);
            background: var(--bg);
            border-bottom: 1px solid #eee;
            position: relative;
        }

        /* Central container that provides horizontal padding. */
        .container {
            width: 100%;
            padding: 0 16px;
            /* Inner padding for mobile. */
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* Horizontal navigation styling. */
        .horizontal-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .horizontal-nav li {
            margin-right: 20px;
        }

        a {
            text-decoration: none;
        }

        a:hover {
            color: #ee9;
        }

        /* Disabled link styling. */
        a.disabled {
            pointer-events: none;
            opacity: 0.5;
            color: #999;
            cursor: not-allowed;
        }

        /* Responsive typography and optional element visibility. */
        @media (min-width:360px) {
            body {
                font-size: 0.62rem;
            }

            .optional {
                display: none;
            }
        }

        @media (min-width:480px) {
            body {
                font-size: 0.75rem;
            }

            .optional {
                display: none;
            }
        }

        @media(min-width:840px) {
            body {
                font-size: 0.9rem;
            }

            .app-header {
                padding: 12px 20px;
                height: 72px
            }

            .optional {
                display: block;
            }
        }

        @media(min-width:1024px) {
            body {
                font-size: 1.25rem;
            }

            .optional {
                display: block;
            }

            .container {
                padding: 0;
                /* Full viewport width on large screens. */
                max-width: none;
            }
        }
    </style>
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

</body>

</html>