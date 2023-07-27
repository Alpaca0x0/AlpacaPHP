<?php
# # # # # # # # # #
# Setting router  #
# # # # # # # # # #
#
# for NginX, put it into configuration file.
#
// location ^~ /AlpacaPHP/ {
//     root /var/www/html/AlpacaPHP;
//     include fastcgi_params;
//     fastcgi_param SCRIPT_FILENAME $document_root/router.php;
//     fastcgi_pass unix:/run/php/php-fpm.sock;
// }
#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # 
#
# DO NOT output any stuff on this page.
#
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

// init
require_once('init.php');

// init router
Inc::clas('Router');
Router::init();

Router::get(['img/', 'js/', 'css/', 'plugin/'], 'asset', Router::path());
Router::get('auth/', 'auth');
Router::get('api/', 'api');
Router::get('sub-page/', 'sub-page');

Router::get('/', 'page', Router::path());

http_response_code(404);
