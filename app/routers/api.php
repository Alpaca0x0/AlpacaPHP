<?php
Router::new(Path::api);

Router::view();

http_response_code(404);