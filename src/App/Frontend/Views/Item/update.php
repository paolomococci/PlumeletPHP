<?php $this->layout("layout", ['title' => 'Item - Update']); ?>

<!-- Contents -->
<section>
    <h3><?= $this->e($view_title) ?></h3>
    <h5><em id="evidence"><?= $this->e($name) ?? 'unset' ?></em></h5>
    <hr>
    <form class="box" method="post">
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
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">
        <button type="submit">Update</button>
    </form>
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <p><a href="/items">items</a></p>
</section>

<style>
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
</style>