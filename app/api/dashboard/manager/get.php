<?php
Inc::clas('resp');
Resp::header();

Inc::clas('manager');
$actor = new Manager();
$actor->id || Resp::error('permission_denied', '請先登入');

Inc::clas('db');
DB::connect() || Resp::error('db_cannot_connect', '無法連線至資料庫');

DB::query('SELECT `m`.`id`, `m`.`account`, `m`.`name`, `r`.`name` AS `role`
    FROM `managers` `m`
    JOIN `roles` `r` ON `r`.`id` = `m`.`role`
    ORDER BY `m`.`id`
;')::execute();
$managers = DB::fetchAll();
$managers !== false || Resp::error('sql_query', '取得帳號清單時發生錯誤');

// 附上「我是否能控制這個帳號」的旗標，讓前端決定是否顯示編輯控制項
foreach($managers as &$m){
    $m['canControl'] = Manager::canControl($actor->role, $m['role']);
}
unset($m);

Resp::success('success', [
    'me' => [
        'id' => $actor->id,
        'account' => $actor->account,
        'name' => $actor->name,
        'role' => $actor->role,
    ],
    'managers' => $managers,
], '取得成功');
