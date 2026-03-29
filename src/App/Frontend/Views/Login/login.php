<!-- Login view -->
<!-- The following instruction is used to change the base layout! -->
<?php $this->layout('auth', ['title' => 'Login']); ?>

<?php
// Error message.
$error = $error ?? '';
?>

<section class="login-section">

    <form action="/login" method="post" class="auth-form" novalidate>
        <?php if ($error != ''): ?>
            <div class="feedback-invalid">
                <?= $this->e($error) ?>
            </div>
        <?php endif; ?>

        <!-- CSRF hidden token -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <!-- 
                In this case I left the display logic in the view itself 
                for the sole purpose of documenting a possibility offered by 
                the PHP development environment.
            -->
            <input
                id="email"
                name="email"
                type="email"
                class="form-field <?= ($error != '') ? 'is-invalid' : '' ?>"
                value="<?= $this->e($form['email'] ?? '') ?>"
                required
                autocomplete="username"
                autofocus>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <!-- 
                In this case I left the display logic in the view itself 
                for the sole purpose of documenting a possibility offered by 
                the PHP development environment.
            -->
            <input
                id="password"
                name="password"
                type="password"
                class="form-field <?= ($error != '') ? 'is-invalid' : '' ?>"
                required
                autocomplete="current-password">
        </div>

        <!-- Remember me checkbox not implemented! -->
        <div class="form-check">
            <input
                id="remember"
                name="remember"
                type="checkbox"
                class="form-check-input"
                <?= isset($form['remember']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn">Login</button>

        <!-- Helper links -->
        <div class="text-helper">
            <a href="/forgot">Forgotten password?</a>
            <br>
            <a href="/register">Register now?</a>
        </div>
    </form>
</section>