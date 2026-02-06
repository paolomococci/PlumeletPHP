<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->e($title) ?></title>
    <!-- <link rel="stylesheet" href="TODO"> -->
</head>

<body>
    <div id="app">
        <!-- 1) Header -->
        <header>
            <span style="margin: 2rem;"><?= $this->e($title) ?></span>
            <!-- toggle nav button -->
            <!-- <button class="sidebar-toggle" aria-label="Open menu" type="button">
                ☰
            </button> -->
        </header>

        <!-- 2) Nav -->
        <nav class="sidebar" style="padding: 3rem;" role="navigation" aria-label="Main menu">
            <ul style="list-style:none;margin:0;padding:0;">
                <li style="margin-bottom:.75rem;">
                    <a href="/home">home</a>
                </li>
                <li style="margin-bottom:.75rem;">
                    <a href="/items">items</a>
                </li>
                <li style="margin-bottom:.75rem;">
                    <a href="/users">users</a>
                </li>
                <li style="margin-bottom:.75rem;">
                    <a href="/warehouses">warehouses</a>
                </li>
            </ul>
        </nav>

        <!-- 3) Main content (my PHP/HTML). -->
        <main role="main">
            <!-- Here, my PHP output is inserted. -->
            <?= $this->section("content") ?>
        </main>
    </div>
    
</body>

</html>