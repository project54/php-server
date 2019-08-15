<?php

use App\Server;

require './vendor/autoload.php';

(new Server(null, null))->listen(8080);
