<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\Middlewares\SessionMiddleware;

class CreateSessionMutation extends AbstractKadathResolver
{
    const KARMA_COST = 0;
    public function doResolve()
    {
        $session = SessionMiddleware::fromRequest($this->context->request);
        if (!$session->getSid()) {
            $session->createSession();
        }

        return $this->export($session);
    }
}
