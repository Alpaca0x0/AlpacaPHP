<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div class="ts-container">
    <img src="<?=Uri::img('cover.png')?>">
    <h1>Index 首頁</h1>
    <span>歡迎您使用 AlpacaPHP。</span>
</div>

<div class="ts-divider is-section"></div>

<div class="ts-container">
    <?php Inc::component('router'); ?>
</div>

<?php Inc::component('footer'); ?>
