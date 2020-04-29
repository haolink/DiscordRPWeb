<?php

use RPWeb\Common\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/config.php';

$kernel = new Kernel($config);
$kernel->start();