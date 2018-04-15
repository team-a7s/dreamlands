<?php

declare(strict_types=1);

namespace Kadath\Middlewares;

use GraphQL\Error\FormattedError;
use Interop\Http\Factory\ResponseFactoryInterface;
use Kadath\Adapters\IncrWithSupremumTtl;
use Kadath\Configuration\KarmaPolicy;
use Kadath\Exceptions\KarmaException;
use Lit\Air\Injection\SetterInjector;
use Lit\Middleware\IpAddress\IpAddress;
use Lit\Nimo\AbstractMiddleware;
use Predis\Client;
use Predis\Profile\RedisProfile;
use Psr\Http\Message\ResponseInterface;

class KarmaMiddleware extends AbstractMiddleware implements KarmaPolicy
{
    const SETTER_INJECTOR = SetterInjector::class;

    const KARMA_PREFIX = 'km:';

    const KARMA_TYPE_ANONYMOUS = 1;
    const KARMA_TYPE_TURING_SESSION = 2;
    const KARMA_TYPE_USER_SESSION = 3;

    const KARMA_COST_GENERAL_REQUEST = 10;
    const KARMA_COST_GENERAL_WRITE = 1000;
    const KARMA_COST_SPAWN = 3000;
    /**
     * @var IpAddress
     */
    protected $ipAddress;

    /**
     * @var SessionMiddleware
     */
    protected $session;
    protected $remainKarma;

    protected $type = self::KARMA_TYPE_ANONYMOUS;
    /**
     * @var Client
     */
    private $redisClient;


    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    public function injectResponseFactory(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        return $this;
    }


    public function __construct(Client $redisClient)
    {
        $profile = $redisClient->getProfile();
        assert($profile instanceof RedisProfile);
        $profile->defineCommand(IncrWithSupremumTtl::ID, IncrWithSupremumTtl::class);

        $this->redisClient = $redisClient;
    }

    public function forceCommit(int $karma): int
    {
        $key = $this->makeKarmaKey();
        $pipeline = $this->redisClient->pipeline();
        $pipeline
            ->set($key, 0, 'EX', self::KARMA_TTL, 'NX')
            ->incrby($key, $karma);

        return $pipeline->execute()[1];
    }

    public function commit(int $karma): int
    {
        assert($karma >= 0);
        $cmd = $this->redisClient->createCommand(IncrWithSupremumTtl::ID, [
            $this->makeKarmaKey(),
            $karma,
            self::KARMA_CAPABILITY[$this->type],
            self::KARMA_TTL,
        ]);

        $result = (int)$this->redisClient->executeCommand($cmd);
        if ($result < 0) {
            throw KarmaException::confess();
        }

        $this->remainKarma = $result;
        return $result;
    }

    /**
     * @return mixed
     */
    public function getRemainKarma()
    {
        if (!$this->remainKarma) {
            $key = $this->makeKarmaKey();
            $karma = intval($this->redisClient->get($key));
            $this->remainKarma = self::KARMA_CAPABILITY[$this->type] - $karma;
        }

        return $this->remainKarma;
    }

    protected function getTtl()
    {
        $key = $this->makeKarmaKey();
        $ttl = $this->redisClient->ttl($key);
        return $ttl > 0 ? $ttl : self::KARMA_TTL;
    }

    protected function main(): ResponseInterface
    {
        $this->attachToRequest();

        $this->ipAddress = IpAddress::fromRequest($this->request);
        $this->session = SessionMiddleware::fromRequest($this->request);
        if ($this->session) {
            if ($this->session->getCurrentUser()) {
                $this->type = self::KARMA_TYPE_USER_SESSION;
            }
        }

        try {
            $response = $this->delegate();
            return $response
                ->withHeader('X-Karma', sprintf('%d/%d', $this->getRemainKarma(), self::KARMA_CAPABILITY[$this->type]))
                ->withHeader('X-Karma-Ttl', $this->getTtl());
        } catch (KarmaException $e) {
            $forbidden = $this->responseFactory->createResponse(403);
            $forbidden->getBody()->write(json_encode([
                'data' => [],
                'errors' => [
                    FormattedError::createFromException($e)
                ],
            ]));

            return $forbidden
                ->withHeader('X-Karma', sprintf('%d/%d', $this->getRemainKarma(), self::KARMA_CAPABILITY[$this->type]))
                ->withHeader('X-Karma-Ttl', $this->getTtl());
        }
    }

    protected function makeKarmaKey()
    {
        switch ($this->type) {
            case self::KARMA_TYPE_ANONYMOUS:
                $ip = $this->ipAddress->getIpAddress();
                $ipInt = ip2long($ip);// don't support IPv6 now
                $key = $ipInt ? 'ip:' . intval($ipInt / 16) : 'ip:-';
                break;
            case self::KARMA_TYPE_USER_SESSION:
                $key = 'u:' . $this->session->getCurrentUser()->id;
                break;
            default:
                $key = 'ip:-';
                break;
        }

        return self::KARMA_PREFIX . $key;
    }
}
