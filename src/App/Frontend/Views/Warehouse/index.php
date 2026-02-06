<? $this->layout("layout", ['title' => 'Warehouse - Index']) ?>

<!-- Contents -->
<section>

    <? if (! empty($warehouses)): ?>
        <!-- view all warehouses section -->
        <section>
            <h3><?= $this->e($view_title) ?></h3>
            <hr>
            <table>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Registered</th>
                        <th>Updated</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($warehouses as $warehouse): ?>
                        <tr>
                            <!-- id of warehouse -->
                            <td><?= $this->e($warehouse->getId() ?? '') ?></td>
                            <!-- read warehouse link -->
                            <td>
                                <a href="/warehouse/<?= $this->e($warehouse->getId() ?? '') ?>">
                                    <?= $this->e($warehouse->getName() ?? '') ?>
                                </a>
                            </td>
                            <!-- address of warehouse -->
                            <td><?= $this->e((string) $warehouse->getAddress()) ?></td>
                            <!-- email of warehouse -->
                            <td><?= $this->e((string) $warehouse->getEmail()) ?></td>
                            <!-- type of warehouse -->
                            <td><?= $this->e((string) $warehouse->getType()) ?></td>
                            <!-- metadata of warehouse -->
                            <td><?= $this->e($warehouse->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                            <td><?= $this->e($warehouse->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                            <!-- update and delete links -->
                            <td><a style="text-decoration: none" href="/warehouse/update/<?= $this->e($warehouse->getId()) ?? '' ?>">edit</a></td>
                            <td><a style="text-decoration: none" href="/warehouse/delete/<?= $this->e($warehouse->getId()) ?? '' ?>">del</a></td>
                        </tr>
                    <? endforeach; ?>
                </tbody>
            </table>
            <h5>today is: <?= $datetime?></h5>
            <hr>
            <p><a href="/warehouse/new">add new warehouse</a></p>
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
            <p><a href="/warehouse/new">add new warehouse</a></p>
        </section>
    <? endif; ?>

</section>