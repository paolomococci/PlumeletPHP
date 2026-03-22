<!-- Register view -->
<?php 
use \App\Util\Handlers\CssClassHandler;
use \App\Util\Helpers\ErrorsFluentRenderHelper;
use \App\Util\Helpers\CssClassFluentRenderHelper;

// The following instruction is used to change the base layout!
$this->layout('auth', ['title' => 'Register']);

// Extract the values from the form.
// Assign default values (empty arrays) to the `$form` and `$errors` variables if they are not already defined.
// This is useful for handling request errors or missing data.
$form   = $form ?? [];
$errors = $errors ?? [];

// Perform initial setup for the form and error variables.
// Retrieve the values submitted from the form (`$_POST`), 
// or use empty strings as defaults if the fields are not present. 
// This prevents `Undefined index` errors.
$name     = $form['name'] ?? '';
$email    = $form['email'] ?? '';
?>

<section class="login-section">

    <form action="/register" method="post" class="auth-form" novalidate>
        <!-- CSRF hidden token -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- Name -->
        <div class="form-group">
            <label for="name">Name</label>
            <!-- Fluent renderer-style CSS class management. -->
            <input
                id="name"
                name="name"
                type="text"
                <?= (new CssClassFluentRenderHelper($errors, 'name'))->withCssClassesAlwaysPresent(['form-field'])->renderClassAttribute() ?>
                value="<?= $this->e($name) ?>"
                required
                autocomplete="name"
                autofocus>
            <!-- Fluent renderer-style error message helper. -->
            <?= (new ErrorsFluentRenderHelper($errors, 'name'))->with(['feedback-invalid'])->render() ?>
        </div>

        <!-- Email / Username -->
        <div class="form-group">
            <label for="email">Email</label>
            <!-- Writing CSS classes in static handler method style. -->
            <input
                id="email"
                name="email"
                type="email"
                <?= CssClassHandler::writeCssClasses(['form-field'], $errors, 'email') ?>
                value="<?= $this->e($email) ?>"
                required
                autocomplete="username">
            <!-- Fluent renderer-style error message helper. -->
            <?= (new ErrorsFluentRenderHelper($errors, 'email'))->with(['feedback-invalid'])->render() ?>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password</label>
            <!-- Writing CSS classes in static handler method style. -->
            <input
                id="password"
                name="password"
                type="password"
                <?= CssClassHandler::writeCssClasses(['form-field'], $errors, 'password') ?>
                required
                autocomplete="new-password">
            <!-- Fluent renderer-style error message helper. -->
            <?= (new ErrorsFluentRenderHelper($errors, 'password'))->with(['feedback-invalid'])->render() ?>
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

        <button type="submit" class="btn">Register</button>

        <!-- Helper links -->
        <div class="text-helper">
            <a href="/login">Already have an account?</a>
            <br>
            <a href="/forgot">Forgotten password?</a>
        </div>
    </form>
</section>