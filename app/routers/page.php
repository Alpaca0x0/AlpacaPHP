<?php
Router::new(Path::page);

Router::equal('/', function () { Router::redirect('/index/'); });

Router::view();

http_response_code(404);
