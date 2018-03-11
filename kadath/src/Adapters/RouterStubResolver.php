<?php

declare(strict_types=1);

namespace Kadath\Adapters;

use Lit\Air\Factory;
use Lit\Bolt\Router\BoltStubResolver;
use Psr\Http\Server\RequestHandlerInterface;

class RouterStubResolver extends BoltStubResolver
{
    public function resolve($stub): RequestHandlerInterface
    {
        if (is_callable([$stub, 'routeHandler'])) {
            $factory = Factory::of($this->container);
            return $factory->invoke([$stub, 'routeHandler']);
        }

        return parent::resolve($stub);
    }
}
