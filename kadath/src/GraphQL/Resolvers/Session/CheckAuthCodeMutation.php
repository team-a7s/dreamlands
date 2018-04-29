<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use Kadath\Database\Repositories\MemberRepo;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\Middlewares\SessionMiddleware;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Github;

class CheckAuthCodeMutation extends AbstractKadathResolver
{
    /**
     * @var Github
     */
    protected $githubProvider;

    public function injectGithubProvider(Github $githubProvider)
    {
        $this->githubProvider = $githubProvider;
        return $this;
    }


    /**
     * @var MemberRepo
     */
    protected $memberRepo;

    public function injectMemberRepo(MemberRepo $memberRepo)
    {
        $this->memberRepo = $memberRepo;
        return $this;
    }

    /**
     * @return SessionMiddleware
     * @throws KadathException
     */
    public function doResolve()
    {
        $sessionMiddleware = $this->context->session();
        $sessionStorage = $this->context->sessionStorage();

        $state = $sessionStorage->get(SessionMiddleware::SESSION_OAUTH_STATE);
        if (!$state) {
            throw KadathException::invalidSession();
        }

        if ($state !== ($this->args['state'] ?? false)) {
            throw KadathException::auth('bad state');
        }

        if (empty($this->args['code'])) {
            throw KadathException::auth('empty code');
        }
        $sessionStorage->delete(SessionMiddleware::SESSION_OAUTH_STATE);

        try {
            $token = $this->githubProvider->getAccessToken('authorization_code', [
                'code' => $this->args['code']
            ]);
        } catch (IdentityProviderException $e) {
            throw KadathException::auth('auth failed');
        }

        $owner = $this->githubProvider->getResourceOwner($token);
        $member = $this->memberRepo->findOrCreate($owner);
        if (!$member->id) {
            $member->last_ip = $this->context->ipAddress()->getIpAddress();
            $this->memberRepo->insert($member);
        }

        $sessionMiddleware->createSession();
        $sessionMiddleware->setLoginMember($member);

        $sessionStorage2 = $sessionMiddleware->getSession();
        $sessionStorage2->set(SessionMiddleware::SESSION_ACCESS_TOKEN, $token->getToken());

        return $sessionMiddleware;
    }
}
