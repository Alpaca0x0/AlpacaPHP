<?php
Inc::clas('resp');
Resp::header();

$username = trim(Type::string($_POST['username'] ?? ''));
$password = Type::string($_POST['password'] ?? '');
$roleRaw = $_POST['role'] ?? '';
$role = ($roleRaw === '' || $roleRaw === null) ? null : Type::int($roleRaw);

(mb_strlen($username) > 0 && mb_strlen($username) <= 64) || Resp::error('username_length_out_of_range', '帳號長度超出範圍');
mb_strlen($password) > 0 || Resp::error('password_required', '請輸入密碼');

Inc::clas('manager');
$actor = Manager::current();
$actor !== false || Resp::error('not_logged_in', '請先登入');

Inc::clas('permission');
// 非 root 只能新增比自己權限低的身分組，且不能新增 root 帳號
if($actor['role'] !== null){
    $role !== null || Resp::error('permission_denied', '你沒有權限新增 root 帳號');
    $actorRoleData = Permission::getRole($actor['role']);
    $newRoleData = Permission::getRole($role);
    ($actorRoleData && $newRoleData && $actorRoleData['rank'] > $newRoleData['rank']) || Resp::error('permission_denied', '你沒有權限指派這個身分組');
}

$id = Manager::add($username, $password, $role);
$id !== false || Resp::error('sql_query', '新增帳號時發生錯誤（帳號可能已存在）');

Resp::success('success', [
    'id' => $id,
    'username' => $username,
    'role' => $role,
], '新增成功');
