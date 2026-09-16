<?php
Inc::clas('resp');
Resp::header();
Inc::clas('manager');

Router::new(Path::api);

// 登入本身必須在未登入時也能呼叫，排除在權限檢查之外
Router::get('dashboard/login/', function(){ Router::view(); });

// api/dashboard/ 底下都是後台功能，一律需要登入
Router::get('dashboard/', function(){
    if(Manager::isLoggedIn()){ return; }
    http_response_code(403);
    Resp::warning('permission_denied', '請先登入');
});

Router::view();

http_response_code(404);
