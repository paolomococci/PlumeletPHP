<!-- Warehouse delete view -->
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
        <li><em>address:</em> <?= $this->e($address) ?? 'unset' ?></li>
        <li><em>email:</em> <?= $this->e($email) ?? 'unset' ?></li>
        <li><em>type:</em> <?= $this->e($type) ?? 'unset' ?></li>
    </ul>
    <form id="postForm" method="post">
        <div>
            <input readonly type="hidden" name="id" id="id" value="<?= isset($id) ? $this->e($id) : 'unset' ?>">
        </div>
        <!-- 
            Hidden field for the CSRF (Cross-Site Request Forgery) token, used to prevent Cross-Site Request Forgery attacks. 
            For security reasons, the value is escaped.
        -->
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">
        <button id="postBtn" class="btn" type="submit">Delete</button>
    </form>
    <hr>
    <p><a href="/warehouses">warehouses</a></p>
</section>

<!-- component that displays a modal to confirm the action to be taken -->
<?php
/*  Insert the reusable confirm component. */
$this->insert('Components/confirm', [
    // Any data to pass to the component.
    // 'todo'   => 'TODO',
]);
?>

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

    /* Button delete: bright red. */
    .btn {
        background: linear-gradient(180deg, #f66 0%, #eee 100%);
        transition:
            background-position 180ms ease,
            box-shadow 180ms ease;
        background-size: 100% 200%;
        background-position: top;
        margin-top: 0.5rem;
    }

    /* hover/focus for accessibility. */
    .btn:hover,
    .btn:focus {
        background-position: bottom;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        outline: none;
    }
</style>