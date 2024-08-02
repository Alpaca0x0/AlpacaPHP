<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div class="ts-container">
    <?php
    Inc::clas('db');
    DB::connect() or die('error - database can not connect.');
    echo ('successfully - database has connected.');
    ?>
</div>

<div class="ts-divider is-section"></div>

<div class="ts-container">
    <?php Inc::component('info'); ?>
</div>

<?php Inc::component('footer'); ?>