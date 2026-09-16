<?php
# # # # # # # # # #
# Setting router  #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # 
#
# DO NOT output any stuff on this page.
#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

// init
require_once('init.php');

// session (must start before any output, so login state works everywhere)
if(session_status() !== PHP_SESSION_ACTIVE){ session_start(); }

// init router
Inc::clas('Router');
Router::init();

// main routers
// securimage 驗證碼圖片是 .php，且 mimeTypes.php 把 php 對應到 text/html，
// 會被下方通用的靜態資源路由（Router::view() 對 text/html 直接 return false）擋下，故獨立於此路由並直接執行。
Router::get('plugin/securimage/securimage_show.php', function(){
    require(LOCAL.Path::asset.'plugin/securimage/securimage_show.php');
    die();
});
Router::get(['img/', 'js/', 'css/', 'plugin/'], 'asset', true);
Router::get('api/', 'api');

Router::get('/', 'page');

http_response_code(404);
