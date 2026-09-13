<?php
Inc::clas('resp');
Resp::header();

$id = Type::int($_POST['id'] ?? 0);
$roleRaw = $_POST['role'] ?? '';
$newRole = ($roleRaw === '' || $roleRaw === null) ? null : Type::int($roleRaw);

$id > 0 || Resp::error('id_out_of_range', 'ID 超出範圍');

Inc::clas('manager');
$actor = Manager::current();
$actor !== false || Resp::error('not_logged_in', '請先登入');

$target = Manager::get($id);
$target !== false || Resp::error('manager_not_found', '找不到該帳號');

Manager::canControl($actor['role'], $target['role']) || Resp::error('permission_denied', '你沒有權限調整這個帳號');

// 避免權限升級：非 root 不可將帳號設為 root，且新身分組必須比自己權限低
if($actor['role'] !== null){
    $newRole !== null || Resp::error('permission_denied', '你沒有權限將帳號設為 root');
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
