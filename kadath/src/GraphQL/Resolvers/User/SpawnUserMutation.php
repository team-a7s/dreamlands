<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\User;

use Kadath\Database\Repositories\UserRepo;
use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\Middlewares\KarmaMiddleware;

/**
 * Class SpawnUser
 * @package Kadath\Resolvers
 */
class SpawnUserMutation extends AbstractKadathResolver
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

    public function doResolve()
    {
        assert(isset($this->args['nickname']) && is_string($this->args['nickname']));
        $this->context->karma()->commit(KarmaMiddleware::KARMA_COST_SPAWN);

        $nickname = $this->args['nickname'];
        $ip = $this->context->ipAddress()->getIpAddress();
        $user = $this->userRepo->spawn($nickname, $ip);

        return [
            'hash' => $user->hash,
            'user' => $user
        ];
    }
}
