<?php

use Kadath\Adapters\KadathLogger;
use Kadath\Kadath;
use Monolog\Handler\ChromePHPHandler;

require(__DIR__ . '/../vendor/autoload.php');

sleep(1);
KadathLogger::instance()->pushHandler(new ChromePHPHandler());
Kadath::run();
