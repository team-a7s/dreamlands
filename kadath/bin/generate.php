#!/usr/bin/env php
<?php

use Kadath\Kadath;
use Lit\Air\Factory;
use Lit\Griffin\ObjectClassGenerator;
use Lit\Griffin\SourceBuilder;

require(__DIR__ . '/../vendor/autoload.php');

Factory::of(Kadath::makeContainer())->invoke(function (SourceBuilder $sourceBuilder, ObjectClassGenerator $generator) {
    $generator->generate($sourceBuilder->build());
});
