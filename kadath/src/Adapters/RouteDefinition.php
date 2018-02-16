<?php

declare(strict_types=1);


namespace Kadath\Adapters;


use FastRoute\RouteCollector;
use Kadath\Action\AvatarAction;
use Lit\Griffin\GraphQLAction;
use Lit\Router\FastRoute\FastRouteDefinition;

class RouteDefinition extends FastRouteDefinition
{
    public function __invoke(RouteCollector $routeCollector): void
    {
        $routeCollector->get('/avatar/{routeId}', [AvatarAction::class]);
        $routeCollector->addRoute(['GET', 'POST'], '/graphql', [GraphQLAction::class]);
    }
}
