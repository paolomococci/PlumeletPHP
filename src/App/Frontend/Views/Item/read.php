<?php $this->layout("layout", ['title' => 'Read - Item']); ?>

    <!-- Contents -->
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <ul>
            <li><em>id:</em> <?= isset($id) ? $this->e($id) : 'unset' ?></li>
            <li><em>name:</em> <?= $this->e($name) ?? 'unset' ?></li>
            <li><em>price:</em> <?= $this->e($price) ?? 'unset' ?></li>
            <li><em>currency:</em> <?= $this->e($currency) ?? 'unset' ?></li>
            <li><em>description:</em> <?= $this->e($description) ?? 'unset' ?></li>
        </ul>
        <hr>
        <p><a href="/items">items</a></p>
    </section>