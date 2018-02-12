<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use Kadath\Database\Repositories\UserRepo;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractKadathResolver;

class LoginMutation extends AbstractKadathResolver
{

    /**
     * @var UserRepo
     */
    protected $userRepo;

    public function injectUserRepo(UserRepo $userRepo)
    {
        $this->userRepo = $userRepo;
        return $this;
    }

    public function resolve()
    {
        assert(isset($this->args['displayName']) && is_string($this->args['displayName']));
        assert(isset($this->args['hash']) && is_string($this->args['hash']));

        $sessionMiddleware = $this->context->session();

        $user = $this->userRepo->byDisplayname($this->args['displayName']);
        if (!$user || $user->hash !== $this->args['hash']) {
            throw KadathException::auth('login failed');
        }

        $sessionMiddleware->createSession();
        $sessionMiddleware->setLogin($user);

        return $sessionMiddleware;
    }
}
