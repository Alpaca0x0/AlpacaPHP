<?php
Inc::clas('resp');
Resp::header();

$name = trim(Type::string($_POST['name'] ?? ''));
$text = trim(Type::string($_POST['text'] ?? ''));

(mb_strlen($name) > 0 && mb_strlen($name) <= 64) || Resp::error('name_length_out_of_range', '名稱長度超出範圍');
(mb_strlen($text) > 0 && mb_strlen($text) <= 64) || Resp::error('text_length_out_of_range', '顯示文字長度超出範圍');

Inc::clas('manager');
$actor = Manager::current();
$actor['role'] === null || Resp::error('permission_denied', '只有 root 可以新增權限');

Inc::clas('permission');
$id = Permission::add($name, $text);
$id !== false || Resp::error('sql_query', '新增權限時發生錯誤');

Resp::success('success', [
    'id' => $id,
    'name' => $name,
    'text' => $text,
], '新增成功');
