<?php $this->layout("layout", ['title' => 'Item - Update']); ?>

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
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" value="<?= isset($price) ? $this->e($price) : 'unset' ?>">
            </div>
            <div>
                <label for="currency">Currency</label>
                <input type="text" name="currency" id="currency" value="<?= isset($currency) ? $this->e($currency) : 'unset' ?>">
            </div>
            <div>
                <label for="description">Description</label>
                <textarea name="description" id="description"><?= isset($description) ? $this->e($description) : 'unset' ?></textarea>
            </div>
            <button type="submit">Update</button>
        </form>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <p><a href="/items">items</a></p>
    </section>