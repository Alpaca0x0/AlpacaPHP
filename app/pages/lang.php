<?php
Inc::clas('lang');
// 允許透過 ?lang=xx-XX 手動切換語言（須在任何輸出前設定 cookie）
if(isset($_GET['lang']) && !headers_sent()){
    setcookie('lang', $_GET['lang'], ['expires' => time() + 60 * 60 * 24 * 365, 'path' => ROOT, 'domain' => DOMAIN]);
    $_COOKIE['lang'] = $_GET['lang'];
}
$lang = new Lang();
$lang->load('common');
?>
<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div class="ts-container">
    <h2><?=htmlentities($lang->get('greeting', ['name' => 'Alpaca']) ?? '')?></h2>
    <p>Lang::code = <code><?=htmlentities($lang->code)?></code></p>

    <div class="ts-space"></div>
    <?php foreach(Inc::config('language') as $code => $text): ?>
        <a class="ts-button is-outlined" href="?lang=<?=urlencode($code)?>"><?=htmlentities($text)?></a>
    <?php endforeach; ?>
</div>

<div class="ts-divider is-section"></div>

<div class="ts-container">
    <?php Inc::component('router'); ?>
</div>

<?php Inc::component('footer'); ?>
