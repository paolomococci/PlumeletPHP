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
    <!-- Common Auth CSS -->
    <link rel="stylesheet" href="/assets/css/auth.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<!-- Main wrapper that extends full width (used for header background). -->

<body id="app" class="full-bleed">
    <!-- 1) Header - visible only on larger viewports due to .optional -->
    <header class="app-header optional" role="banner">
        <!-- Page title rendered inside the header. -->
        <span><?= $this->e($title) ?></span>
    </header>


    <div class="container">
        <!-- 2) Main content area - the view-specific content is injected here. -->
        <main role="main">
            <!-- Render the content section defined in the child template. -->
            <?= $this->section("content") ?>
        </main>
    </div>
</body>

</html>