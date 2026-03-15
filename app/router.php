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

// init router
Inc::clas('Router');
Router::init();

// main routers
Router::get(['img/', 'js/', 'css/', 'plugin/'], 'asset', true);
Router::get('api/', 'api');
Router::get('admin/', 'admin');
Router::get('forest/', 'nature');

Router::get('/', 'page');

http_response_code(404);
