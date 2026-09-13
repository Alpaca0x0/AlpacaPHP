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
    'request' => [
        'text' => 'Request',
        'link' => Uri::page('request/'),
    ],
    'lang' => [
        'text' => 'Lang',
        'link' => Uri::page('lang/'),
    ],
    'permission' => [
        'text' => 'Permission',
        'link' => Uri::page('permission/'),
    ],
    'auth' => $me === false ? [
        'text' => 'Login',
        'link' => Uri::page('login/'),
    ] : [
        'text' => 'Logout ('.$me['username'].')',
        'link' => Uri::page('logout/'),
    ],
];
