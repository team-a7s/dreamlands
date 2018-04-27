<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Model;

use Kadath\GraphQL\AbstractKadathModel;
use Kadath\GraphQL\Resolvers\Board\PostMutation;
use Kadath\GraphQL\Resolvers\Session\ChallengeCaptchaMutation;
use Kadath\GraphQL\Resolvers\Session\CheckAuthCodeMutation;
use Kadath\GraphQL\Resolvers\Session\CreateAuthUrlMutaion;
use Kadath\GraphQL\Resolvers\Session\CreateSessionMutation;
use Kadath\GraphQL\Resolvers\Session\LoginMutation;
use Kadath\GraphQL\Resolvers\User\SpawnUserMutation;

class MutationModel extends AbstractKadathModel
{
    const KARMA_COST = 0;
    protected static $resolverMap = [
        'createSession' => CreateSessionMutation::class,
        'spawnUser' => SpawnUserMutation::class,
        'login' => LoginMutation::class,
        'createAuthUrl' => CreateAuthUrlMutaion::class,
        'checkAuthCode' => CheckAuthCodeMutation::class,
        'challengeCaptcha' => ChallengeCaptchaMutation::class,

        'post' => PostMutation::class,
    ];
}
