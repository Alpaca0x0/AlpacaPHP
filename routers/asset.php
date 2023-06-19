<?php
Router::new(Path::asset);

Router::view();

http_response_code(404);