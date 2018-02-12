<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\Middlewares\SessionMiddleware;

class CreateSessionMutation extends AbstractKadathResolver
{
    public function resolve()
    {
        $session = SessionMiddleware::fromRequest($this->context->request);
        $session->createSession();

        return $this->export($session);
    }
}
