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
Router::get(['img/', 'js/', 'css/', 'plugin/'], 'asset', true);
Router::get('api/', 'api');

Router::get('/', 'page');

http_response_code(404);
