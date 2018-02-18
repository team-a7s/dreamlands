<?php

use Kadath\Adapters\KadathLogger;
use Kadath\Kadath;
use Monolog\Handler\ChromePHPHandler;

require(__DIR__ . '/../vendor/autoload.php');

KadathLogger::instance()->pushHandler(new ChromePHPHandler());
Kadath::run();
