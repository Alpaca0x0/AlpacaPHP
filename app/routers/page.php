<?php
Router::new(Path::page);

// 根目錄一律導向後台
Router::equal('/', function(){ Router::redirect('dashboard/'); });

// '/dashboard/' 開頭的路徑交給 routers/dashboard.php 負責（含登入驗證）
Router::get('dashboard/', function(){ Inc::router('dashboard'); });

Router::view();

http_response_code(404);
