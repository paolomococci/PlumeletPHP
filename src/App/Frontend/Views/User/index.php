<? $this->layout("layout", ['title' => 'User - Index']) ?>

<!-- Contents -->
<section>

    <? if (! empty($users)): ?>
        <!-- view all users section -->
        <section>
            <h3><?= $this->e($view_title) ?></h3>
            <hr>
            <table>
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
                <tbody>
                    <? foreach ($users as $user): ?>
                        <tr>
                            <!-- id of user -->
                            <td><?= $this->e($user->getId() ?? '') ?></td>
                            <!-- read user link -->
                            <td>
                                <a href="/user/<?= $this->e($user->getId() ?? '') ?>">
                                    <?= $this->e($user->getName() ?? '') ?>
                                </a>
                            </td>
                            <!-- email of user -->
                            <td><?= $this->e((string) $user->getEmail()) ?></td>
                            <!-- metadata of user -->
                            <td><?= $this->e($user->getCreatedAt()->format('d/m/Y \a\t H:i') ?? '') ?></td>
                            <td><?= $this->e($user->getUpdatedAt()->format('jS \of F Y \a\t H:i:s') ?? '') ?></td>
                            <!-- update and delete links -->
                            <td><a style="text-decoration: none" href="/user/update/<?= $this->e($user->getId()) ?? '' ?>">edit</a></td>
                            <td><a style="text-decoration: none" href="/user/delete/<?= $this->e($user->getId()) ?? '' ?>">del</a></td>
                        </tr>
                    <? endforeach; ?>
                </tbody>
            </table>
            <h5>today is: <?= $datetime?></h5>
            <hr>
            <p><a href="/user/new">add new user</a></p>
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
            <p><a href="/user/new">add new user</a></p>
        </section>
    <? endif; ?>

</section>