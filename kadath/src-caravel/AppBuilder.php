<?php

declare(strict_types=1);

namespace Lit\Caravel;

use Lit\Air\Factory;
use Lit\Air\Injection\SetterInjector;
use Lit\Bolt\BoltContainer;
use Lit\Caravel\Commands\ConvertCommand;
use Lit\Caravel\Commands\DiffCommand;
use Lit\Caravel\Commands\ListPlanCommand;
use Lit\Caravel\Commands\PlanCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

class AppBuilder
{
    const SETTER_INJECTOR = SetterInjector::class;

    /**
     * @var BoltContainer
     */
    protected $container;

    public function injectContainer(BoltContainer $container)
    {
        $this->container = $container;
        return $this;
    }

    public function build(): Application
    {
        $application = new Application('caravel', '1.0');
        $this->register($application);
        return $application;
    }

    protected function register(Application $application)
    {
        $factories = $this->factoryMap([
            'convert' => ConvertCommand::class,
            'diff' => DiffCommand::class,
            'list-plan' => ListPlanCommand::class,
            'plan' => PlanCommand::class,
        ]);

        $application->setCommandLoader(new FactoryCommandLoader($factories));
    }

    protected function factoryMap($commands)
    {
        return array_combine(array_keys($commands), array_map(function ($class) {
            return function () use ($class) {
                return Factory::of($this->container)->getOrProduce($class);
            };
        }, $commands));
    }
}
