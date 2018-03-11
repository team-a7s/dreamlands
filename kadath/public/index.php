<?php

use Kadath\Adapters\KLogger;
use Kadath\Kadath;
use Monolog\Handler\ChromePHPHandler;

require(__DIR__ . '/../vendor/autoload.php');

KLogger::instance()->pushHandler(new ChromePHPHandler());
Kadath::run();
