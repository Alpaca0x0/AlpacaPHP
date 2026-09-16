<?php
Inc::clas('resp');
Resp::header();

Inc::clas('permission');
$roles = Permission::getAllRoles();
$roles !== false || Resp::error('sql_query', '取得身分組清單時發生錯誤');

Resp::success('success', $roles, '取得成功');
