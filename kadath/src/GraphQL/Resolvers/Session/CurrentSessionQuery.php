<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use Kadath\GraphQL\AbstractKadathResolver;

class CurrentSessionQuery extends AbstractKadathResolver
{
    public function resolve()
    {
        $sessionMiddleware = $this->context->session();

        return $sessionMiddleware->getSid()
            ? $this->context->session()
            : null;
    }
}
