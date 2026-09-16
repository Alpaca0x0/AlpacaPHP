<?php
Inc::clas('resp');
Resp::header();

$username = trim(Type::string($_POST['username'] ?? ''));
$password = Type::string($_POST['password'] ?? '');

$username !== '' || Resp::error('username_required', '請輸入帳號');
$password !== '' || Resp::error('password_required', '請輸入密碼');

Inc::clas('manager');
Manager::login($username, $password) || Resp::error('login_failed', '帳號或密碼錯誤');

Resp::success('success', Manager::current(), '登入成功');
