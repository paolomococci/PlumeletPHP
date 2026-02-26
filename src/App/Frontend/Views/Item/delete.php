<?php $this->layout("layout", ['title' => 'Items - Confirm Delete']); ?>

<!-- Contents -->
<section>
    <h3><?= $this->e($view_title) ?></h3>
    <h5><em id="evidence"><?= $this->e($name) ?? 'unset' ?></em></h5>
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <ul class="box">
        <li><em>id:</em> <?= isset($id) ? $this->e($id) : 'unset' ?></li>
        <li><em>name:</em> <?= $this->e($name) ?? 'unset' ?></li>
        <li><em>price:</em> <?= $this->e($price) ?? 'unset' ?></li>
        <li><em>currency:</em> <?= $this->e($currency) ?? 'unset' ?></li>
        <li><em>description:</em> <?= $this->e($description) ?? 'unset' ?></li>
    </ul>
    <form method="post">
        <div>
            <input readonly type="hidden" name="id" id="id" value="<?= isset($id) ? $this->e($id) : 'unset' ?>">
        </div>
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">
        <button type="submit">Delete</button>
    </form>
    <hr>
    <p><a href="/items">items</a></p>
</section>

<style>
    #evidence {
        /* mix 80% of the original color with 20% red */
        background-color: color-mix(in srgb, var(--bg) 80%, red 20%);
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        border-radius: 0.25rem;
    }

    .box {
        /* mix 80% of the original color with 20% red */
        background-color: color-mix(in srgb, var(--bg) 80%, red 20%);
        list-style: none;
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        border-radius: 0.25rem;
    }
</style>