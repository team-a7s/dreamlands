#!/usr/bin/env php
<?php

declare(strict_types=1);

use Lit\Air\Factory;
use Lit\Caravel\AppBuilder;
use Lit\Nexus\Utilities\Inspector;
use Psr\Container\ContainerInterface;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($_ENV['CONTAINER_CLASS']) || !is_subclass_of($_ENV['CONTAINER_CLASS'], ContainerInterface::class)) {
    echo 'bad $CONTAINER_CLASS', PHP_EOL;
    die(-1);
}

Factory::of(new $_ENV['CONTAINER_CLASS']())->invoke(function (AppBuilder $appBuilder) {
    Inspector::setGlobalHandler();
    $appBuilder->build()->run();
});
