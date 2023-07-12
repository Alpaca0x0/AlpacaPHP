<?php
Router::new(Path::page);

Router::equal('/', function () { Router::redirect('/index/'); });
Router::get('index/', function () { Router::view('index'); });
if(DEBUG) Router::equal('lab/', function () { Router::view('lab'); });

Router::view();

http_response_code(404);