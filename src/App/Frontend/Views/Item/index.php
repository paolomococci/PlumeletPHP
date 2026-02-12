<? $this->layout("layout", ['title' => 'Item - Index']) ?>

<!-- Contents -->
<section>

    <? if (! empty($items)): ?>
        <!-- view all items section -->
        <section>
            <h3><?= $this->e($view_title) ?></h3>
            <hr>
            <table>
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
                <tbody>
                    <? foreach ($items as $item): ?>
                        <tr>
                            <!-- id of item -->
                            <td><?= $this->e($item->getId() ?? '') ?></td>
                            <!-- read item link -->
                            <td>
                                <a href="/item/<?= $this->e($item->getId() ?? '') ?>">
                                    <?= $this->e($item->getName() ?? '') ?>
                                </a>
                            </td>
                            <!-- price of item -->
                            <td><?= $this->e((string) $item->getPrice() ?? '') ?></td>
                            <!-- currency of item -->
                            <td><?= $this->e((string) $item->getCurrency() ?? '') ?></td>
                            <!-- description of item -->
                            <td><?= $this->e($item->getDescription() ?? '') ?></td>
                            <!-- metadata of item -->
                            <td><?= $this->e($item->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                            <td><?= $this->e($item->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                            <!-- update and delete links -->
                            <td><a style="text-decoration: none" href="/item/update/<?= $this->e($item->getId()) ?? '' ?>">edit</a></td>
                            <td><a style="text-decoration: none" href="/item/delete/<?= $this->e($item->getId()) ?? '' ?>">del</a></td>
                        </tr>
                    <? endforeach; ?>
                </tbody>
            </table>
            <h5>today is: <?= $datetime?></h5>
            <hr>
            <p><a href="/item/new">add new item</a></p>
        </section>
    <? else: ?>
        <!-- empty/fallback section -->
        <section>
            <h3><?= $this->e($view_title) ?></h3>
            <hr>
            <h5>No results found!</h5>
            <hr>
            <h5>today is: <?= $datetime?></h5>
            <hr>
            <p><a href="/item/new">add new item</a></p>
        </section>
    <? endif; ?>

</section>