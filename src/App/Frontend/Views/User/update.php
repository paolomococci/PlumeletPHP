<?php $this->layout("layout", ['title' => 'User - Update']); ?>

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
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= isset($email) ? $this->e($email) : 'unset' ?>">
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password">
            </div>
            <button type="submit">Update</button>
        </form>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <p><a href="/users">users</a></p>
    </section>