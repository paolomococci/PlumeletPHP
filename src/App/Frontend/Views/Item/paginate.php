<?php $this->layout("layout", ['title' => 'Item - Paginate']) ?>

<section>
<?php if (!empty($items)): ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <table>
            <!-- table header -->
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <!-- table body -->
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $this->e($item->getId() ?? '') ?></td>
                    <td><a href="/item/<?= $this->e($item->getId() ?? '') ?>">
                        <?= $this->e($item->getName() ?? '') ?>
                    </a></td>
                    <td><?= $this->e((string) $item->getPrice() ?? '') ?></td>
                    <td><?= $this->e((string) $item->getCurrency() ?? '') ?></td>
                    <td><?= $this->e($item->getDescription() ?? '') ?></td>
                    <td><?= $this->e($item->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                    <td><?= $this->e($item->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                    <td><a href="/item/update/<?= $this->e($item->getId()) ?? '' ?>">edit</a></td>
                    <td><a href="/item/delete/<?= $this->e($item->getId()) ?? '' ?>">del</a></td>
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

        <p><a href="/item/new">add new item</a></p>
    </section>
<?php else: ?>
    <section>
        <h3><?= $this->e($view_title) ?></h3>
        <hr>
        <h5>No results found!</h5>
        <p><a href="/item/new">add new item</a></p>
    </section>
<?php endif; ?>
</section>