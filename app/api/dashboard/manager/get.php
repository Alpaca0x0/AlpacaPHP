<?php
Inc::clas('resp');
Resp::header();

Inc::clas('manager');
$actor = Manager::current();

$managers = Manager::getAll();
$managers !== false || Resp::error('sql_query', '取得帳號清單時發生錯誤');

// 附上「我是否能控制這個帳號」的旗標，讓前端決定是否顯示編輯控制項
foreach($managers as &$m){
    $m['canControl'] = Manager::canControl($actor['role'], $m['role']);
}
unset($m);

Resp::success('success', [
    'me' => $actor,
    'managers' => $managers,
], '取得成功');
