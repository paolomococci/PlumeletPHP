<?php
// Tell the renderer to use the same dashboard layout that you use for the main
// user-area.  You could also create a dedicated layout (e.g. 'auth') - the idea
// is that the page should still look like the rest of the site.
$this->layout('auth', ['title' => 'Logged Out']);
?>

<section class="logout-container">
    <!-- Title -->
    <h2 class="logout-header">
        You have successfully logged out
    </h2>

    <!-- Hint / call-to-action -->
    <h5 class="logout-message">
        If you want to <a href="/login">log in</a> again, click the link above.
    </h5>
</section>

<!-- Optional - move the styles below to an external file (e.g. assets/css/logout.css) -->
<style>
    /* Centered, full-width flex container */
    .logout-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        /* vertical centering */
        text-align: center;
        min-height: 70vh;
        /* 70% of viewport height */
        padding: 2rem;
    }

    .logout-header {
        margin-bottom: 1rem;
        font-size: 2rem;
        /* slightly muted color - feel free to change */
        color: #a33;
    }

    .logout-message a {
        color: #06f;
        text-decoration: none;
        font-weight: bold;
    }

    .logout-message a:hover {
        text-decoration: underline;
    }

    .logout-message {
        font-size: 1.2rem;
        color: #333;
        max-width: 600px;
    }
</style>