<?php
Inc::clas('resp');
Resp::header();

$id = Type::int($_POST['id'] ?? 0);
$roleRaw = $_POST['role'] ?? '';
$newRole = ($roleRaw === '' || $roleRaw === null) ? null : Type::int($roleRaw);

$id > 0 || Resp::error('id_out_of_range', 'ID 超出範圍');

Inc::clas('manager');
$actor = Manager::current();

$target = Manager::get($id);
$target !== false || Resp::error('manager_not_found', '找不到該帳號');

$id !== $actor['id'] || Resp::error('permission_denied', '不能調整自己的身分組');
Manager::canControl($actor['role'], $target['role']) || Resp::error('permission_denied', '你沒有權限調整這個帳號');

// 避免權限升級：沒有人（包含 root）可以把帳號設為 root；非 root 還必須設為比自己權限低的身分組
$newRole !== null || Resp::error('permission_denied', '不能將帳號設為 root');
if($actor['role'] !== null){
    Inc::clas('permission');
    $actorRoleData = Permission::getRole($actor['role']);
    $newRoleData = Permission::getRole($newRole);
    ($actorRoleData && $newRoleData && $actorRoleData['rank'] > $newRoleData['rank']) || Resp::error('permission_denied', '你沒有權限指派這個身分組');
}

$result = Manager::setRole($id, $newRole);
$result || Resp::error('sql_query', '更新身分組時發生錯誤');

Resp::success('success', [
    'id' => $id,
    'role' => $newRole,
], '更新成功');
