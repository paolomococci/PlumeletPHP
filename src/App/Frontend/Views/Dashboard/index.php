<!-- Index dashboard view. -->
<!-- The following instruction is used to change the base layout! -->
<?php

use App\Backend\Models\Factories\OperatorFactory;

$pageTitle = $pageTitle ?? 'Dashboard';

$this->layout('dashboard', ['title' => $pageTitle]);

$csrfToken = $csrf_token ?? 'unset';
$userId = $userId ?? 'unset';

// Create the operator object from the user ID.
$operator = OperatorFactory::create($userId);

/**
 * If for some strange reason a user ID that does not exist has been passed, 
 * it destroys the session and performs a temporary redirect to the application login.
 */
if ($operator->getEmail() === null && $operator->getAuth() === null) {
    session_destroy();
    header('Location: /', true, 302);
    exit();
}
?>

<!-- Centered dashboard container. -->
<div class="dashboard-container">
    <!-- Header: shows the logged-in user. -->
    <h2 class="dashboard-header">
        Welcome, <?= $this->e($operator->getEmail()) ?>!
    </h2>
    <p>ID: <?= $this->e($operator->getId()) ?></p>
    <p>email: <?= $this->e($operator->getEmail()) ?></p>
    <p>auth: <?= $this->e($operator->getRole()) ?></p>

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