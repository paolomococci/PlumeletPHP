<?php

// Import the `CurrencyEnum` enumeration from a specific namespace. 
// This allows you to manage available currencies (e.g., EUR, USD) 
// as constants or predefined values.
use App\Backend\Models\Enums\CurrencyEnum;

// Extract the values from the form.
// Assign default values (empty arrays) to the `$form` and `$errors` variables if they are not already defined. 
// This is useful for handling request errors or missing data.
$form   = $form ?? [];
$errors = $errors ?? [];

// Perform initial setup for the form and error variables.
// Retrieve the values submitted from the form (`$_POST`), 
// or use empty strings as defaults if the fields are not present. 
// This prevents `Undefined index` errors.
$name        = $form['name'] ?? '';
$price       = $form['price'] ?? '';
$currency    = $form['currency'] ?? '';
$description = $form['description'] ?? '';

// Set the page layout.
// Load the main page layout (`layout.php`) with a dynamic title 'Store'. 
// This is common in mature frameworks.
$this->layout('layout', ['title' => 'Store']);
?>

<!-- Item create view -->
<!-- 
    This code is representative of a PHP application employing a separation 
    of concerns between the backend and frontend, 
    utilizing an MVC framework and a standard template engine.
-->
<section>
    <!-- HTML page structure. -->
    <!-- Begin the main section of the page with a dynamic title (`$view_title`) and an HTML form for creating a new item. -->
    <h3><?= $this->e($view_title) ?></h3>
    <hr>

    <form class="box" method="post" novalidate>
        <!-- Name -->
        <!-- Display an input field for the product name. -->
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

        <!-- Price -->
        <!-- Numeric input for the price, configured with a step of `0.01` to allow for cent-level precision. -->
        <div class="<?= !empty($errors['price']) ? 'error' : '' ?>">
            <label for="price">Price</label>
            <!-- 
                Format the price as a float with 2 decimal places using a dot as separator; 
                if the result is empty or falsy, default to '0.00'
            -->
            <input
                type="number"
                step="0.01"
                name="price"
                id="price"
                value="<?= $this->e(number_format((float)$price, 2, '.', '') ?: '0.00') ?>"
                required>
            <?php if (!empty($errors['price'])): ?>
                <small class="error-msg"><?= $this->e($errors['price']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Currency -->
        <!-- 
            Currency input field (text type) with a `<datalist>` offering predefined currency choices 
            (e.g., EUR, USD) taken from the `CurrencyEnum`. 
            The `CurrencyEnum::cases()` method returns all the values of the enumeration.
            It's worth noting that `::cases()` is a built-in method in PHP 8 for enumerations, 
            which allows you to retrieve an array containing all the instances of the defined cases within the enum.
            If an error occurs for the 'name' field (`$errors['name']`), 
            apply the `error` CSS class and display an error message in red.
        -->
        <div class="<?= !empty($errors['currency']) ? 'error' : '' ?>">
            <label for="currency">Currency</label>
            <input
                type="text"
                name="currency"
                id="currency"
                list="currencyList"
                value="<?= $this->e($currency) ?>"
                placeholder="EUR, USD, …"
                autocomplete="off"
                required />

            <datalist id="currencyList">
                <?php foreach (CurrencyEnum::cases() as $case): ?>
                    <option value="<?= $this->e($case->value) ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <!-- Providing Functionality for Users with JavaScript Disabled and ensuring Accessibility in Browsers Without JavaScript. -->
            <!-- 
                If the browser does not support JavaScript, display a <select> dropdown with the same options as the <datalist>.
                Automatically select the currency that is already present in the `$currency` variable.
            -->
            <noscript>
                <select name="currency" id="currency" required>
                    <?php foreach (CurrencyEnum::cases() as $case): ?>
                        <?php
                        $value        = $case->value;
                        $label        = strtoupper($value);
                        $selectedAttr = ($value === $currency) ? 'selected' : '';
                        ?>
                        <option value="<?= $this->e($value) ?>" <?= $selectedAttr ?>>
                            <?= $this->e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </noscript>

            <?php if (!empty($errors['currency'])): ?>
                <small class="error-msg"><?= $this->e($errors['currency']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <!-- Text area for the product description. The `required` attribute ensures that this field is filled in. -->
        <div class="<?= !empty($errors['description']) ? 'error' : '' ?>">
            <label for="description">Description</label>
            <textarea name="description" id="description" required><?= $this->e($description) ?></textarea>
            <?php if (!empty($errors['description'])): ?>
                <small class="error-msg"><?= $this->e($errors['description']) ?></small>
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
        <button type="submit">Store</button>
    </form>

    <!-- Additional information. -->
    <!-- Displays the current date (`$datetime`); `$this->e()` ensures data is properly escaped. -->
    <h5>today is: <?= $this->e($datetime) ?></h5>
    <hr>
    <!-- Display a link to return to the list of items. -->
    <p><a href="/items">back</a></p>
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
</style>