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
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" value="0.00">
            </div>
            <div>
                <label for="currency">Currency</label>
                <input type="text" name="currency" id="currency">
            </div>
            <div>
                <label for="description">Description</label>
                <textarea name="description" id="description"></textarea>
            </div>
            <button type="submit">Store</button>
        </form>
        <h5>today is: <?= $datetime ?></h5>
        <hr>
        <p><a href="/items">back</a></p>
    </section>