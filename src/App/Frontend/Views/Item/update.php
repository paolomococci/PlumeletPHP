<!-- Item update view -->
<?php

use App\Backend\Models\Enums\CurrencyEnum;

// Normalization and default values.
$form   = $form   ?? [];
$errors = $errors ?? [];

// Retrieve values from the form, or use empty values, to avoid undefined indices.
$id          = $form['id']          ?? '';
$name        = $form['name']        ?? '';
$description = $form['description'] ?? '';
$price       = $form['price']       ?? '';
$currency    = $form['currency']    ?? '';

// Set the layout.
$this->layout('layout', ['title' => 'Edit Item']);
?>

<section>
    <!-- ------------------------ HEADER ------------------------ -->
    <h3><?= $this->e($view_title) ?></h3>
    <h5 style="<?= ($name === '') ? 'display:none' : '' ?>"><em id="evidence"><?= $this->e($name) ?? 'unset' ?></em></h5>

    <!-- ------------------------ FORM ------------------------ -->
    <form id="postForm" class="box" method="post" action="">
        <!-- Hidden ID - required for the update. -->
        <input type="hidden" name="id" value="<?= $this->e($id) ?>">

        <!-- Item name. -->
        <div class="<?= !empty($errors['name']) ? 'error' : '' ?>">
            <label for="name">Name</label>
            <input type="text"
                name="name"
                id="name"
                value="<?= $this->e($name) ?>"
                required>
            <?php if (!empty($errors['name'])): ?>
                <small class="error-msg"><?= $this->e($errors['name']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div class="<?= !empty($errors['description']) ? 'error' : '' ?>">
            <label for="description">Description</label>
            <textarea name="description" id="description" required><?= $this->e($description) ?></textarea>
            <?php if (!empty($errors['description'])): ?>
                <small class="error-msg"><?= $this->e($errors['description']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Price -->
        <div class="<?= !empty($errors['price']) ? 'error' : '' ?>">
            <label for="price">Price</label>
            <input type="text"
                name="price"
                id="price"
                value="<?= $this->e(number_format((float)($price ?? 0), 2, '.', '')) ?>"
                required
                pattern="^\d+(\.\d{2})$"
                inputmode="decimal">
            <?php if (!empty($errors['price'])): ?>
                <small class="error-msg"><?= $this->e($errors['price']) ?></small>
            <?php endif; ?>
        </div>

        <!-- Currency (datalist + noscript) -->
        <div class="<?= !empty($errors['currency']) ? 'error' : '' ?>">
            <label for="currency">Currency</label>
            <input type="text"
                name="currency"
                id="currency"
                list="currencyList"
                value="<?= $this->e($currency) ?>"
                placeholder="EUR, USD, …"
                autocomplete="off"
                required>

            <datalist id="currencyList">
                <?php foreach (CurrencyEnum::cases() as $case): ?>
                    <option value="<?= $this->e($case->value) ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <noscript>
                <select name="currency" id="currency" required>
                    <?php foreach (CurrencyEnum::cases() as $case): ?>
                        <?php
                        $value        = $case->value;
                        $label        = strtoupper($value);
                        $selectedAttr = ($value === $currency) ? 'selected' : '';
                        ?>
                        <option value="<?= $this->e($value) ?>" <?= $selectedAttr ?>><?= $this->e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </noscript>

            <?php if (!empty($errors['currency'])): ?>
                <small class="error-msg"><?= $this->e($errors['currency']) ?></small>
            <?php endif; ?>
        </div>

        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- Submit button. -->
        <button id="postBtn" class="btn" type="submit">Update</button>
    </form>

    <!-- Info -->
    <h5>today is: <?= $this->e($datetime) ?></h5>
    <hr>
    <p><a href="/items">back to list</a></p>
</section>

<!-- component that displays a modal to confirm the action to be taken -->
<?php
/*  Insert the reusable confirm component. */
$this->insert('Components/confirm', [
    // Any data to pass to the component.
    'confirmTitle' => 'You are about to edit the item data.',
    'alertMsg' => 'Are you sure you updated the item data correctly?',
]);
?>

<style>
    /* Styles for the layout and appearance of the form fields. */
    #evidence {
        /* mix 90% of the original color with 10% red */
        background-color: color-mix(in srgb, var(--bg) 90%, red 10%);
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        border-radius: 0.25rem;
    }

    .box {
        /* mix 95% of the original color with 5% red */
        background-color: color-mix(in srgb, var(--bg) 95%, red 5%);
        list-style: none;
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

    /* Button update: bright red. */
    .btn {
        background: linear-gradient(180deg, #f66 0%, #eee 100%);
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

    /* required fields tooltip is placed in the center of the view. */
    .rf-tooltip {
        position: fixed;
        width: 90%;
        max-width: 420px;
        background: #fff;
        border: 1px solid #f44;
        color: #000;
        padding: 1rem 1.2rem;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        z-index: 10000;
        transform: translate(-50%, -50%);
        /* Will be overwritten with JavaScript. */
        display: none;
        pointer-events: auto;
    }
</style>

<!-- /assets/js/checkRequiredFieldTooltip.js -->
<script type="module">
    import { loadTooltipStyles, attachTooltip } from '/assets/js/checkRequiredFieldTooltip.js';

    // Load the tooltip stylesheet.
    loadTooltipStyles();

    // Attach the tooltip to the form.
    attachTooltip('#postForm');

    // In case I need to apply the same tooltip on multiple forms:
    // document.querySelectorAll('form').forEach(f => attachTooltip(f));
</script>