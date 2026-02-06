<?php $this->layout("layout", ['title' => 'Warehouses - delete']); ?>

    <!-- Contents -->
    <section>
        <h3>Delete</h3>
        <h5>today is: <?= $datetime ?></h5>
        <p>Lorem ipsum dolor sit amet, consectetur. Officia molestiae ratione, illum qui cupiditate repudiandae est ex ea sunt illo ad aperiam deleniti rem asperiores minus autem laborum voluptates nesciunt sequi quia, soluta voluptatibus eligendi animi maxime! Optio dolores non possimus vero earum asperiores ad hic, adipisci cum? Vero ut nostrum quae earum ducimus ad aliquam debitis molestiae voluptas consequatur sint delectus explicabo, quod esse sit iusto tempore hic aspernatur quidem qui harum et quas repellendus adipisci. Sint iure amet molestiae ipsam odit qui facilis minima dignissimos eum, praesentium maxime eaque consequuntur magni beatae illo vero rerum ad asperiores reprehenderit!</p>
        <hr>
        <p><em>feedback:</em> <?php echo isset($message) ? $this->e($message) : 'unset'?></p>
        <hr>
        <p><a href="/warehouses">warehouses</a> <a href="/products">products</a></p>
    </section>