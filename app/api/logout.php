<?php
Inc::clas('resp');
Resp::header();

Inc::clas('manager');
Manager::logout();

Resp::success('success', null, '登出成功');
