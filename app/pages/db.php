<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div class="ts-container">
    <?php
    Inc::clas('db');
    echo (DB::connect() ? 'successfully - database has connected.' : 'error - database can not connect.');
    ?>
</div>

<div class="ts-divider is-section"></div>

<div class="ts-container">
    <?php Inc::component('info'); ?>
</div>

<?php Inc::component('footer'); ?>