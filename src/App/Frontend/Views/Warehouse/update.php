<!-- Warehouse update view -->
<?php

use App\Backend\Models\Enums\WarehouseTypeEnum;

// Normalization and default values.
$form   = $form   ?? [];
$errors = $errors ?? [];

// Retrieve values from the form, or use empty values, to avoid undefined indices.
$id             = $form['id']       ?? '';
$name           = $form['name']     ?? '';
$address        = $form['address']  ?? '';
$email          = $form['email']    ?? '';
$selected       = $form['type']     ?? '';

// Set the layout.
$this->layout("layout", ['title' => 'Warehouse - Update']);
?>

<section>
    <!-- ------------------------ HEADER ------------------------ -->
    <h3><?= $this->e($view_title) ?></h3>
    <h5 style="<?= ($name === '') ? 'display:none' : '' ?>"><em id="evidence"><?= $this->e($name) ?? 'unset' ?></em></h5>

    <!-- ------------------------ FORM ------------------------ -->
    <form id="postForm" class="box" method="post" action="">
        <!-- Hidden ID – required for the update. -->
        <input type="hidden" name="id" value="<?= $this->e($id) ?>">

        <!-- Warehouse name. -->
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

        <!-- Type -->
        <div>
            <label for="warehouseType">Type</label>
            <select name="warehouseType" id="warehouseType">
                <option value="<?= isset($type) ? $this->e($type) : WarehouseTypeEnum::OWNED->value ?>">- <?= isset($type) ? $this->e(ucfirst(strtolower($type))) : 'Choose a type' ?> -</option>
                <!-- WarehouseTypeEnum::cases(): array<WarehouseTypeEnum>, Returns an array with all defined cases. -->
                <?php foreach (WarehouseTypeEnum::cases() as $case): ?>
                    <?php
                    // The value to be saved in the database.
                    $value        = $case->value;
                    // owned, supplier or currier
                    $label        = ucfirst(strtolower($value));
                    $selectedAttr = ($value === $selected) ? 'selected' : '';
                    ?>
                    <option value="<?= $this->e($value) ?>" <?= $this->e($selectedAttr) ?>>
                        <?= $this->e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($errors['type'])): ?>
                <small class="error-msg"><?= $this->e($errors['type']) ?></small>
            <?php endif; ?>
        </div>

        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">

        <!-- Submit button. -->
        <button id="postBtn" class="btn" type="submit">Update</button>
    </form>

    <!-- Info -->
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <p><a href="/warehouses">warehouses</a></p>
</section>

<!-- component that displays a modal to confirm the action to be taken -->
<?php
/*  Insert the reusable confirm component. */
$this->insert('Components/confirm', [
    // Any data to pass to the component.
    'alertMsg' => 'Are you sure you have entered all the updated data?',
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
</style>