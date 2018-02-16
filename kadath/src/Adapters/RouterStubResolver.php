<?php

declare(strict_types=1);

namespace Kadath\Adapters;

use Lit\Air\Factory;
use Lit\Core\Interfaces\RouterStubResolverInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterStubResolver implements RouterStubResolverInterface
{
    /**
     * @var Factory
     */
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function resolve($stub): RequestHandlerInterface
    {
        if ($stub instanceof RequestHandlerInterface) {
            return $stub;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->factory->instantiate(...$stub);
    }
}
