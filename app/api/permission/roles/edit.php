<?php
Inc::clas('resp');
Resp::header();

$id = Type::int($_POST['id'] ?? 0);
$text = trim(Type::string($_POST['text'] ?? ''));
$rank = Type::int($_POST['rank'] ?? 0);

$id > 0 || Resp::error('id_out_of_range', 'ID 超出範圍');
(mb_strlen($text) > 0 && mb_strlen($text) <= 64) || Resp::error('text_length_out_of_range', '顯示文字長度超出範圍');

Inc::clas('manager');
$actor = Manager::current();
$actor !== false || Resp::error('not_logged_in', '請先登入');
$actor['role'] === null || Resp::error('permission_denied', '只有 root 可以編輯身分組');

Inc::clas('permission');
$result = Permission::editRole($id, $text, $rank);
$result || Resp::error('sql_query', '編輯身分組時發生錯誤');

Resp::success('success', [
    'id' => $id,
    'text' => $text,
    'rank' => $rank,
], '編輯成功');
