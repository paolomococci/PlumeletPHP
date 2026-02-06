<?php $this->layout("layout", ['title' => 'Read - Warehouse']); ?>

    <!-- Contents -->
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <ul>
            <li><em>id:</em> <?= isset($id) ? $this->e($id) : 'unset' ?></li>
            <li><em>name:</em> <?= $this->e($name) ?? 'unset' ?></li>
            <li><em>address:</em> <?= $this->e($address) ?? 'unset' ?></li>
            <li><em>email:</em> <?= $this->e($email) ?? 'unset' ?></li>
            <li><em>type:</em> <?= $this->e($type) ?? 'unset' ?></li>
        </ul>
        <hr>
        <p><a href="/warehouses">warehouses</a></p>
    </section>