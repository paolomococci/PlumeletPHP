<?php $this->layout("layout", ['title' => 'Warehouse - Paginate']) ?>

<section>
<?php if (!empty($warehouses)): ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <table>
            <!-- table header -->
            <tbody>
            <?php foreach ($warehouses as $warehouse): ?>
                <tr>
                    <td><?= $this->e($warehouse->getId() ?? '') ?></td>
                    <td><a href="/warehouse/<?= $this->e($warehouse->getId() ?? '') ?>">
                        <?= $this->e($warehouse->getName() ?? '') ?>
                    </a></td>
                    <td><?= $this->e((string) $warehouse->getAddress() ?? '') ?></td>
                    <td><?= $this->e((string) $warehouse->getEmail() ?? '') ?></td>
                    <td><?= $this->e($warehouse->getType() ?? '') ?></td>
                    <td><?= $this->e($warehouse->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                    <td><?= $this->e($warehouse->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                    <td><a href="/warehouse/update/<?= $this->e($warehouse->getId()) ?? '' ?>">edit</a></td>
                    <td><a href="/warehouse/delete/<?= $this->e($warehouse->getId()) ?? '' ?>">del</a></td>
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

        <p><a href="/warehouse/new">add new warehouse</a></p>
    </section>
<?php else: ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <h5>No results found!</h5>
        <p><a href="/warehouse/new">add new warehouse</a></p>
    </section>
<?php endif; ?>
</section>