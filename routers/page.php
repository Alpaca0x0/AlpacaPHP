<?php
Router::new(Path::page);

Router::equal('/', function () { Router::view('index'); });
Router::equal('index/', function () { Router::redirect('/'); });
if(DEBUG) Router::equal('lab/', function () { Router::view('lab'); });

Router::view();

http_response_code(404);