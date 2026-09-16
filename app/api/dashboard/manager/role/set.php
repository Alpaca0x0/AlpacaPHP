<?php
Inc::clas('resp');
Resp::header();

$id = Type::int($_POST['id'] ?? 0);
$newRole = Type::string($_POST['role'] ?? '');

$id > 0 || Resp::error('id_out_of_range', 'ID 超出範圍');

Inc::clas('manager');
Inc::clas('permission');
Permission::getRoleByName($newRole) || Resp::error('format_not_match', 'role', '身分組不正確');

$actor = new Manager();
$actor->id || Resp::error('permission_denied', '請先登入');

$target = new Manager(id: $id);
$target->id || Resp::error('manager_not_found', '找不到該帳號');

$id !== $actor->id || Resp::error('permission_denied', '不能調整自己的身分組');

// 必須同時能控制這個帳號「目前」的身分組，以及有權限指派「新的」身分組
// （沒有人（包含 root）可以把帳號設為 root；非 root 還必須設為比自己權限低的身分組）
Manager::canControl($actor->role, $target->role) || Resp::error('permission_denied', '你沒有權限調整這個帳號');
Manager::canControl($actor->role, $newRole) || Resp::error('permission_denied', '你沒有權限指派這個身分組');

$result = Manager::setRole($id, $newRole);
$result || Resp::error('sql_query', '更新身分組時發生錯誤');

Resp::success('success', [
    'id' => $id,
    'role' => $newRole,
], '更新成功');
