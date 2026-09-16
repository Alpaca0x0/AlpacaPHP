<?php
Inc::clas('manager');

// 登入/登出頁本身必須在未登入時也能開啟，排除在權限檢查之外
Router::get('dashboard/manager/login/', function(){ Router::view(); });
Router::get('dashboard/manager/logout/', function(){ Router::view(); });

// 其餘 dashboard/ 底下的頁面都要先登入，未登入導向登入頁並夾帶 redirect 參數
Router::get('dashboard/', function(){
    if(Manager::isLoggedIn()){ return; }
    if(!headers_sent()){
        header('Location: '.Uri::page('dashboard/manager/login/').'?redirect='.urlencode(Router::path().Router::parmsStr()));
    }
    die();
});

// dashboard/ 首頁導向預設頁面
Router::equal('dashboard/', function(){ Router::redirect('dashboard/permission/'); });

Router::view();

http_response_code(404);
