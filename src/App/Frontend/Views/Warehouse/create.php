<?php

use App\Backend\Models\Enums\WarehouseTypeEnum;

$this->layout("layout", ['title' => 'Store']);
$selected = $parameters['warehouseType'] ?? '';
?>

<!-- Contents -->
<section>
    <h3><?= $this->e($view_title) ?></h3>
    <hr>
    <form method="post">
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name">
        </div>
        <div>
            <label for="address">Address</label>
            <input type="text" name="address" id="address">
        </div>
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email">
        </div>
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
        </div>
        <button type="submit">Store</button>
    </form>
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <p><a href="/warehouses">back</a></p>
</section>