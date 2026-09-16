<?php
Inc::clas('resp');
Resp::header();

Inc::clas('permission');
$permissions = Permission::getAll();
$permissions !== false || Resp::error('sql_query', '取得權限清單時發生錯誤');

Resp::success('success', $permissions, '取得成功');
