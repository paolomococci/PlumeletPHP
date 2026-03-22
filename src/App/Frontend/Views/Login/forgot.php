<!-- Forgot password view -->
<?php 
use \App\Util\Helpers\ErrorsFluentRenderHelper;
use \App\Util\Helpers\CssClassFluentRenderHelper;

// The following instruction is used to change the base layout!
$this->layout('auth', ['title' => 'Forgot Password']);

// Extract the values from the form.
$form   = $form ?? [];

// This is useful for handling request errors or missing data.
$errors = $errors ?? [];
?>

<section class="login-section">

    <form action="/forgot" method="post" class="auth-form" novalidate>
        <!-- CSRF hidden token -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <!-- Fluent renderer-style CSS class management. -->
            <input
                id="email"
                name="email"
                type="email"
                <?= (new CssClassFluentRenderHelper($errors, 'email'))->withCssClassesAlwaysPresent(['form-field'])->renderClassAttribute() ?>
                value="<?= $this->e($form['email'] ?? '') ?>"
                required
                autocomplete="username">
            <!-- Fluent renderer-style error message helper. -->
            <?= (new ErrorsFluentRenderHelper($errors, 'email'))->with(['feedback-invalid'])->render() ?>
        </div>

        <button type="submit" class="btn">Send reset passphrase</button>

        <!-- Helper links -->
        <div class="text-helper">
            <a href="/login">Back to login</a>
        </div>
    </form>
</section>