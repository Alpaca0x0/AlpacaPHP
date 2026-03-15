<?php
Router::new(Path::page);

Router::view();

http_response_code(404);
