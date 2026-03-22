<!-- Index dashboard view. -->
<!-- The following instruction is used to change the base layout! -->
<?php $this->layout('dashboard', ['title' => 'Dashboard']); ?>

<?php 

$userName = $userName ?? 'friend';
$csrfToken = $csrf_token ?? 'unset';
?>

<!-- Centered dashboard container. -->
<div class="dashboard-container">
    <!-- Header: shows the logged-in user. -->
    <h2 class="dashboard-header">
        Welcome, <?= $this->e($userName) ?>!
    </h2>

    <!-- Message: tells the operator what they are using. -->
    <h5 class="dashboard-message">
        You are now using the <strong>PlumeletPHP framework management system</strong>.
    </h5>

    <!-- Token test for development -->
    <h6>token: <?= $this->e($csrfToken) ?></h6>
</div>

<!-- Inline styles - you can move this to a CSS file if you prefer -->
<style>
    /* Full-width, centered flex container. */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* horizontally center */
        justify-content: center;
        /* vertically center */
        text-align: center;
        /* center the text inside */
        min-height: 70vh;
        /* a nice viewport height */
        padding: 2rem;
    }

    .dashboard-header {
        margin-bottom: 1rem;
        font-size: 2rem;
        color: #235;
    }

    .dashboard-message {
        font-size: 1.2rem;
        color: #345;
        max-width: 600px;
        /* keep the text readable */
    }
</style>