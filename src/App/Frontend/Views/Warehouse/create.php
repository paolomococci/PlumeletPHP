<!-- Warehouse create view -->
<?php

// Import the `CurrencyEnum` enumeration from a specific namespace. 
// This allows you to manage available currencies (e.g., EUR, USD) 
// as constants or predefined values.
use App\Backend\Models\Enums\WarehouseTypeEnum;

// Extract the values from the form.
// Assign default values (empty arrays) to the `$form` and `$errors` variables if they are not already defined. 
// This is useful for handling request errors or missing data.
$form   = $form ?? [];
$errors = $errors ?? [];

// Perform initial setup for the form and error variables.
// Retrieve the values submitted from the form (`$_POST`), 
// or use empty strings as defaults if the fields are not present. 
// This prevents `Undefined index` errors.
$name          = $form['name'] ?? '';
$address       = $form['address'] ?? '';
$email         = $form['email'] ?? '';
$warehouseType = $form['warehouseType'] ?? '';

// Set the page layout.
// Load the main page layout (`layout.php`) with a dynamic title 'Store'. 
// This is common in mature frameworks.
$this->layout('layout', ['title' => 'Store']);
?>

<!-- Warehouse create view -->
<!-- 
    This code is representative of a PHP application employing a separation 
    of concerns between the backend and frontend, 
    utilizing an MVC framework and a standard template engine.
-->
<section>
    <h3><?= $this->e($view_title) ?></h3>
    <hr>
    <form id="postForm" class="box" method="post" novalidate>
        <!-- Name -->
        <!-- Display an input field for the warehouse name. -->
        <!-- `$this->e()` is a function used to escape data in order to prevent Cross-Site Scripting (XSS) attacks. -->
        <div class="<?= !empty($errors['name']) ? 'error' : '' ?>">
            <label for="name">Name</label>
            <input
                type="text"
                name="name"
                id="name"
                value="<?= $this->e($name) ?>"
                required>
            <?php if (!empty($errors['name'])): ?>
                <small class="error-msg"><?= $this->e($errors['name']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Address -->
        <!-- Display an input field for the warehouse address. -->
        <!-- `$this->e()` is a function used to escape data in order to prevent Cross-Site Scripting (XSS) attacks. -->
        <div class="<?= !empty($errors['address']) ? 'error' : '' ?>">
            <label for="address">Address</label>
            <input
                type="text"
                name="address"
                id="address"
                value="<?= $this->e($address) ?>"
                required>
            <?php if (!empty($errors['address'])): ?>
                <small class="error-msg"><?= $this->e($errors['address']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <!-- Display an input field for the warehouse email. -->
        <!-- `$this->e()` is a function used to escape data in order to prevent Cross-Site Scripting (XSS) attacks. -->
        <div class="<?= !empty($errors['email']) ? 'error' : '' ?>">
            <label for="email">Email</label>
            <input
                type="text"
                name="email"
                id="email"
                value="<?= $this->e($email) ?>"
                required>
            <?php if (!empty($errors['email'])): ?>
                <small class="error-msg"><?= $this->e($errors['email']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <!-- Display an input field for the warehouse type. -->
        <div>
            <label for="warehouseType">Type</label>
            <select name="warehouseType" id="warehouseType">
                <option value="<?= WarehouseTypeEnum::OWNED->value ?>">- <?= ucfirst(strtolower(WarehouseTypeEnum::OWNED->value)) ?> -</option>
                <!-- WarehouseTypeEnum::cases(): array<WarehouseTypeEnum>, Returns an array with all defined cases. -->
                <?php foreach (WarehouseTypeEnum::cases() as $case): ?>
                    <?php
                    // The value to be saved in the database.
                    $value        = $case->value;
                    // (owned, supplier or currier)
                    $label        = ucfirst(strtolower($value));
                    $selectedAttr = ($value === $selected) ? 'selected' : '';
                    ?>
                    <option value="<?= $this->e($value) ?>" <?= $this->e($selectedAttr) ?>>
                        <?= $this->e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errors['warehouseType'])): ?>
                <small class="error-msg"><?= $this->e($errors['warehouseType']) ?></small>
            <?php endif; ?>
        </div>

        <!-- 
            Hidden field for the CSRF (Cross-Site Request Forgery) token, used to prevent Cross-Site Request Forgery attacks. 
            For security reasons, the value is escaped.
        -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- 
            Button to submit the form to the backend. The `type="submit"` attribute indicates that the form can be submitted.
        -->
        <button id="postBtn" class="btn" type="submit">Store</button>
    </form>

    <!-- Additional information. -->
    <!-- Displays the current date (`$datetime`); `$this->e()` ensures data is properly escaped. -->
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <!-- Display a link to return to the list of warehouses. -->
    <p><a href="/warehouses">back</a></p>
</section>

<!-- component that displays a modal to confirm the action to be taken -->
<?php
/*  Insert the reusable confirm component. */
$this->insert('Components/confirm', [
    // Any data to pass to the component.
    // 'todo'   => 'TODO',
]);
?>

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

    /* Classes to apply red colors and borders when there are form validation errors. */
    .error input,
    .error textarea,
    .error select,
    .error small {
        border-color: #c22;
    }

    .error-msg {
        color: #c22;
        font-size: 0.7rem;
    }

    /* Button create: bright red. */
    .btn {
        background: linear-gradient(180deg, #6f6 0%, #eee 100%);
        transition:
            background-position 180ms ease,
            box-shadow 180ms ease;
        background-size: 100% 200%;
        background-position: top;
        margin-top: 0.5rem;
    }

    /* hover/focus for accessibility. */
    .btn:hover,
    .btn:focus {
        background-position: bottom;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        outline: none;
    }
</style>