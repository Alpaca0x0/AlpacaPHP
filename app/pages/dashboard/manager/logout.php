<?php
Inc::clas('manager');
Manager::logout();
Router::redirect('dashboard/manager/login/');
