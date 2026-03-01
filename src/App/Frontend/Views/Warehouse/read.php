<?php $this->layout("layout", ['title' => 'Read - Warehouse']); ?>

<!-- Contents -->
<section>
    <h3><?= $this->e($view_title) ?></h3>
    <h5><em id="evidence"><?= $this->e($name) ?? 'unset' ?></em></h5>
    <h5>today is: <?= $datetime ?></h5>
    <hr>
    <ul class="box">
        <li><em>id:</em> <?= isset($id) ? $this->e($id) : 'unset' ?></li>
        <li><em>name:</em> <?= $this->e($name) ?? 'unset' ?></li>
        <li><em>address:</em> <?= $this->e($address) ?? 'unset' ?></li>
        <li><em>email:</em> <?= $this->e($email) ?? 'unset' ?></li>
        <li><em>type:</em> <?= $this->e($type) ?? 'unset' ?></li>
    </ul>
    <hr>
    <p><a href="/warehouses">warehouses</a></p>
</section>

<style>
    #evidence {
        /* mix 80% of the original color with 20% green */
        background-color: color-mix(in srgb, var(--bg) 80%, green 20%);
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        border-radius: 0.25rem;
    }

    .box {
        /* mix 90% of the original color with 10% green */
        background-color: color-mix(in srgb, var(--bg) 90%, green 10%);
        list-style: none;
        padding: 0.25rem 0.5rem;
        margin: 0.25rem;
        border-radius: 0.25rem;
    }
</style>