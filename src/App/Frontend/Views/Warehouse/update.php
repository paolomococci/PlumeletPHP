<?php 

    use App\Backend\Models\Enums\WarehouseType;

    $this->layout("layout", ['title' => 'Warehouse - Update']); 
    $selected = $parameters['warehouseType'] ?? '';
?>

    <!-- Contents -->
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <form method="post">
            <div>
                <label for="id">ID</label>
                <input readonly type="text" name="id" id="id" value="<?= isset($id) ? $this->e($id) : 'unset' ?>">
            </div>
            <div>
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="<?= isset($name) ? $this->e($name) : 'unset' ?>">
            </div>
            <div>
                <label for="address">Address</label>
                <input type="text" name="address" id="address" value="<?= isset($address) ? $this->e($address) : 'unset' ?>">
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= isset($email) ? $this->e($email) : 'unset' ?>">
            </div>
            <div>
                <label for="warehouseType">Type</label>
                <select name="warehouseType" id="warehouseType">
                    <option value="<?= isset($type) ? $this->e($type) : WarehouseType::OWNED->value ?>">- <?= isset($type) ? $this->e(ucfirst(strtolower($type))) : 'Choose a type' ?> -</option>
                        <!-- WarehouseType::cases(): array<WarehouseType>, Returns an array with all defined cases. -->
                        <?php foreach (WarehouseType::cases() as $case): ?>
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
            <button type="submit">Update</button>
        </form>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <p><a href="/warehouses">warehouses</a></p>
    </section>