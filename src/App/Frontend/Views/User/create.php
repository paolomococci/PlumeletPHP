<?php $this->layout("layout", ['title' => 'Store']); ?>

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
                <label for="email">Email</label>
                <input type="email" name="email" id="email">
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password">
            </div>
            <button type="submit">Store</button>
        </form>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <p><a href="/users">back</a></p>
    </section>