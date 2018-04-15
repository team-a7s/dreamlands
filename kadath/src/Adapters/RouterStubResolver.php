<?php

declare(strict_types=1);

namespace Kadath\Adapters;

use Lit\Bolt\Router\BoltStubResolver;
use Lit\Nimo\Handlers\MiddlewareIncluedHandler;
use Lit\Nimo\MiddlewarePipe;
use Psr\Http\Server\RequestHandlerInterface;

class RouterStubResolver extends BoltStubResolver
{
    public function resolve($stub): RequestHandlerInterface
    {
        $action = parent::resolve($stub);
        if (is_callable([$stub, 'prependMiddleware'])) {
            $pipe = new MiddlewarePipe();
            call_user_func([$stub, 'prependMiddleware'], $pipe);
            return new MiddlewareIncluedHandler($action, $pipe);
        }

        return $action;
    }
}
