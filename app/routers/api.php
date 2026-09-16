<?php
Inc::clas('resp');
Resp::header();
Inc::clas('manager');

Router::new(Path::api);

// 登入本身必須在未登入時也能呼叫，排除在權限檢查之外
Router::get('dashboard/login/', function(){ Router::view(); });

// api/dashboard/ 底下都是後台功能，一律需要登入
// 後端授權需要以資料庫驗證 token（Manager::current() 只讀 session，不做這件事），故實體化 Manager
Router::get('dashboard/', function(){
    if((new Manager())->id){ return; }
    http_response_code(403);
    Resp::warning('permission_denied', 'Permission denied');
});

Router::view();

http_response_code(404);
