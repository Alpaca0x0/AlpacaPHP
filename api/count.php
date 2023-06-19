<?php
Inc::clas('resp');
Resp::header();
Inc::clas('db');

DB::connect() or Resp::error('db_connect', '資料庫無法連接');

$sql = "SELECT COUNT(`id`) AS `count` FROM `url`;";
DB::query($sql)::execute();
if(DB::error()){ Resp::error('db_query', '資料庫查詢時發生錯誤'); }
$row = DB::fetch();
if($row === false){ Resp::warning('not_found', '查無資料'); }
if(!isset($row['count'])){ Resp::error('db_fetch', '資料處理時發生錯誤'); }

Resp::success('success', $row['count'] , '查詢成功');
