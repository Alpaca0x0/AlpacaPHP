<?php
Inc::clas('db');
DB::connect() or die('error - database can not connect.');
die('successfully - database has connected.');
