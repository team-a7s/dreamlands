<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\Middlewares\SessionMiddleware;
use Kadath\Utility\IdGeneratorInterface;
use League\OAuth2\Client\Provider\Github;

class CreateAuthUrlMutaion extends AbstractKadathResolver
{
    /**
     * @var IdGeneratorInterface
     */
    protected $idGenerator;
    /**
     * @var Github
     */
    protected $githubProvider;

    public function injectIdGenerator(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
        return $this;
    }

    public function injectGithubProvider(Github $githubProvider)
    {
        $this->githubProvider = $githubProvider;
        return $this;
    }


    public function resolve()
    {
        $url = $this->githubProvider->getAuthorizationUrl();
        $this->context->sessionStorage()->set(
            SessionMiddleware::SESSION_OAUTH_STATE,
            $this->githubProvider->getState()
        );

        return $url;
    }
}
