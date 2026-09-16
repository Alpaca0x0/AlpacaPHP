<?php
Inc::clas('resp');
Resp::header();

$account = strtolower(trim(Type::string($_POST['account'] ?? '')));
$password = Type::string($_POST['password'] ?? '');
$name = trim(Type::string($_POST['name'] ?? ''));
$role = Type::string($_POST['role'] ?? '');

Inc::clas('manager');
Inc::clas('permission');
$config = Inc::config('manager');
preg_match($config['account'], $account) || Resp::error('format_not_match', 'account', '帳號格式不正確');
preg_match($config['password'], $password) || Resp::error('format_not_match', 'password', '密碼格式不正確');
preg_match($config['name'], $name) || Resp::error('format_not_match', 'name', '名稱格式不正確');
Permission::getRoleByName($role) || Resp::error('format_not_match', 'role', '身分組不正確');

$actor = new Manager();
$actor->id || Resp::error('permission_denied', '請先登入');

// 沒有人（包含 root）可以透過這個功能新增 root 帳號；非 root 還必須新增比自己權限低的身分組
Manager::canControl($actor->role, $role) || Resp::error('permission_denied', '你沒有權限指派這個身分組');

$id = Manager::add($account, $password, $name, $role);
$id !== false || Resp::error('sql_query', '新增帳號時發生錯誤（帳號可能已存在）');

Resp::success('success', [
    'id' => $id,
    'account' => $account,
    'name' => $name,
    'role' => $role,
], '新增成功');
