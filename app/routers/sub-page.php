<?php
Router::new(Path::page.'sub-page/');

Router::equal('/', function () { Router::view('sub'); });

Router::view();

http_response_code(404);
