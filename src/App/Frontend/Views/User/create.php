<?php

// Set the page layout.
// Load the main page layout (`layout.php`) with a dynamic title 'Store'. 
// This is common in mature frameworks.
$this->layout("layout", ['title' => 'Store']);
?>

<!-- User create view -->
<!-- 
    This code is representative of a PHP application employing a separation 
    of concerns between the backend and frontend, 
    utilizing an MVC framework and a standard template engine.
-->
<section>
    <h3><?= $this->e($view_title) ?></h3>
    <hr>
    <form class="box" method="post" novalidate>
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name">
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email">
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password">
        </div>
        <!-- 
            Hidden field for the CSRF (Cross-Site Request Forgery) token, used to prevent Cross-Site Request Forgery attacks. 
            For security reasons, the value is escaped.
        -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- 
            Button to submit the form to the backend. The `type="submit"` attribute indicates that the form can be submitted.
        -->
        <button type="submit">Store</button>
    </form>
    <!-- Additional information. -->
    <!-- Displays the current date (`$datetime`); `$this->e()` ensures data is properly escaped. -->
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <!-- Display a link to return to the list of users. -->
    <p><a href="/users">back</a></p>
</section>

<!-- CSS styles specific to this view. -->
<style>
    /* Styles for the layout and appearance of the form fields. */
    .box {
        /* mix 90% of the original color with 10% green */
        background-color: color-mix(in srgb, var(--bg) 90%, green 10%);
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        border-radius: 0.25rem;
    }
</style>