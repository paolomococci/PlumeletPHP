<?php $this->layout("layout", ['title' => 'User - Paginate']) ?>

<section>
<?php if (!empty($users)): ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <table>
            <!-- table header -->
             <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Updated</th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <!-- table body -->
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $this->e($user->getId() ?? '') ?></td>
                    <td><a href="/user/<?= $this->e($user->getId() ?? '') ?>">
                        <?= $this->e($user->getName() ?? '') ?>
                    </a></td>
                    <td><?= $this->e((string) $user->getEmail() ?? '') ?></td>
                    <td><?= $this->e($user->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                    <td><?= $this->e($user->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                    <td><a href="/user/update/<?= $this->e($user->getId()) ?? '' ?>">edit</a></td>
                    <td><a href="/user/delete/<?= $this->e($user->getId()) ?? '' ?>">del</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- pagination bar -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($pagination['prev'] !== null): ?>
                    <li><a href="?page=<?= $pagination['prev'] ?>">« Prev</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                    <li <?= $i === $pagination['current'] ? 'class="active"' : '' ?>>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagination['next'] !== null): ?>
                    <li><a href="?page=<?= $pagination['next'] ?>">Next »</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <p><a href="/user/new">add new user</a></p>
    </section>
<?php else: ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <h5>No results found!</h5>
        <p><a href="/user/new">add new user</a></p>
    </section>
<?php endif; ?>
</section>