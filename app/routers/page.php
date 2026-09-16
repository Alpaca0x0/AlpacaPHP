<?php
Router::new(Path::page);

// 根目錄依登入狀態直接導向登入頁或後台首頁；
// 直接判斷、只轉一次址，避免再繞經 dashboard/ 讓 routers/dashboard.php 的登入判斷又轉址一次
Router::equal('/', function(){
    Inc::clas('manager');
    Router::redirect((new Manager())->id ? 'dashboard/permission/' : 'dashboard/manager/login/');
});

// '/dashboard/' 開頭的路徑交給 routers/dashboard.php 負責（含登入驗證）
Router::get('dashboard/', function(){ Inc::router('dashboard'); });

Router::view();

http_response_code(404);
