<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Session;

use GuzzleHttp\Client;
use Kadath\Adapters\KLogger;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\Kadath;
use Lit\Air\Injection\SetterInjector;

class ChallengeCaptchaMutation extends AbstractKadathResolver
{
    const SETTER_INJECTOR = SetterInjector::class;
    const KARMA_COST = 0;


    /**
     * @var Client
     */
    protected $httpClient;

    public function injectHttpClient(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function resolve()
    {
        $response = $this->httpClient->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'response' => $this->args['response'],
                'secret' => $_ENV[Kadath::ENV_RECAPTCHA_SECRET],
                'remoteip' => $this->context->ipAddress()->getIpAddress(),
            ],
            'timeout' => 3,
        ]);
        if (200 !== $response->getStatusCode()) {
            throw KadathException::badRequest('cannot verify your response');
        }
        KLogger::instance()->info('xxx', $this->args);

        return 42;
    }
}
