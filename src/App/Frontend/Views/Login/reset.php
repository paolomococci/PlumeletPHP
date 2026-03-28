<!-- Reset password view -->
<!-- The following instruction is used to change the base layout! -->
<?php
$this->layout('auth', ['title' => 'Reset Password']);

// Message
$message = $message ?? '';

// Email
$email = $email ?? '';

// Extract the values from the form.
$form  = $form ?? [];

// This is useful for handling request errors or missing data.
$errors = $errors ?? [];

$passphrase = $passphrase ?? ''; // TODO: For debugging only!
?>

<section class="login-section">

    <form action="/reset" method="post" class="auth-form" novalidate>

        <?php if (isset($error)): ?>
            <p class="feedback feedback-danger"><?= $this->e($error) ?></p>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <p class="feedback feedback-success"><?= $this->e($message) ?></p>
        <?php endif; ?>

        <!-- CSRF hidden token -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">
        <!-- hidden email address -->
        <input type="hidden" name="email" value="<?= $this->e($email) ?>">
        <?php if (isset($errors['email'])): ?>
            <div class="feedback-invalid">
                <?= $this->e($errors['email']) ?>
            </div>
        <?php endif; ?>

        <!-- New password -->
        <div class="form-group">
            <label for="password">New password</label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-field <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                required
                autocomplete="new-password">
            <?php if (isset($errors['password'])): ?>
                <div class="feedback-invalid">
                    <?= $this->e($errors['password']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pass-phrase (sent by e-mail) -->
        <div class="form-group">
            <label for="passphrase">Pass-phrase</label>
            <input
                id="passphrase"
                name="passphrase"
                type="text"
                class="form-field <?= isset($errors['passphrase']) ? 'is-invalid' : '' ?>"
                required
                autocomplete="off">
            <?php if (isset($errors['passphrase'])): ?>
                <div class="feedback-invalid">
                    <?= $this->e($errors['passphrase']) ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn">Reset password</button>

        <!-- Helper link -->
        <div class="text-helper">
            <a href="/login">Back to login</a>
        </div>
    </form>
</section>

<!-- TODO: For debugging only! -->
<section>
    <?php if ($passphrase !== ''): ?>
        <p class="feedback feedback-success"><?= $this->e($passphrase) ?></p>
    <?php endif; ?>
</section>