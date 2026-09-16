<?php
Inc::clas('resp');
Resp::header();

$id = Type::int($_POST['id'] ?? 0);
$text = trim(Type::string($_POST['text'] ?? ''));

$id > 0 || Resp::error('id_out_of_range', 'ID 超出範圍');
(mb_strlen($text) > 0 && mb_strlen($text) <= 64) || Resp::error('text_length_out_of_range', '顯示文字長度超出範圍');

Inc::clas('manager');
$actor = Manager::current();
$actor['role'] === null || Resp::error('permission_denied', '只有 root 可以編輯權限');

Inc::clas('permission');
$result = Permission::edit($id, $text);
$result || Resp::error('sql_query', '編輯權限時發生錯誤');

Resp::success('success', [
    'id' => $id,
    'text' => $text,
], '編輯成功');
