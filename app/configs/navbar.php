<?php
Inc::clas('manager');
$me = Manager::current();

return [
    'index' => [
        'text' => 'Index',
        'link' => Uri::page('index/'),
    ],
    'db' => [
        'text' => 'DB',
        'link' => Uri::page('db/'),
    ],
    'lang' => [
        'text' => 'Lang',
        'link' => Uri::page('lang/'),
    ],
    'permission' => [
        'text' => 'Permission',
        'link' => Uri::page('dashboard/permission/'),
    ],
    'auth' => $me === false ? [
        'text' => 'Login',
        'link' => Uri::page('dashboard/manager/login/'),
    ] : [
        'text' => 'Logout ('.$me['username'].')',
        'link' => Uri::page('dashboard/manager/logout/'),
    ],
];
