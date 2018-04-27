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
        $uri = $_ENV[Kadath::ENV_RECAPTCHA_URL];
        $response = $this->httpClient->post($uri, [
            'form_params' => [
                'response' => $this->args['response'],
                'secret' => $_ENV[Kadath::ENV_RECAPTCHA_SECRET],
                'remoteip' => $this->context->ipAddress()->getIpAddress(),
            ],
            'timeout' => 3,
        ]);
        if (200 !== $response->getStatusCode()) {
            throw KadathException::badRequest('cannot verify your rKarmaMiddlewareesponse');
        }

        $contents = $response->getBody()->getContents();
        KLogger::instance()->info('res', ['bod' => $contents]);
        $json = json_decode($contents);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \AssertionError('bad response from g');
        }
        KLogger::instance()->info('xxx', [$this->args, $json]);

        $this->context->karma()->activeTuringSession();
        return $this->context->karma()->getRemainKarma();
    }
}
