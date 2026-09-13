<?php
Inc::clas('resp');
Resp::header();

Inc::clas('manager');
Manager::isLoggedIn() || Resp::error('not_logged_in', '請先登入');

Inc::clas('permission');
$roles = Permission::getAllRoles();
$roles !== false || Resp::error('sql_query', '取得身分組清單時發生錯誤');

Resp::success('success', $roles, '取得成功');
