<?php
Router::new(Path::asset);

Router::get('img/', function(){ Router::enter('img/'); });
Router::get('js/', function(){ Router::enter('js/'); });
Router::get('css/', function(){ Router::enter('css/'); });
Router::get('plugin/', function(){ Router::enter('plugin/'); });

Router::view();

http_response_code(404);