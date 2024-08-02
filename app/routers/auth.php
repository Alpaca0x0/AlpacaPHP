<?php
Router::new(Path::auth);

Router::view();

http_response_code(404);