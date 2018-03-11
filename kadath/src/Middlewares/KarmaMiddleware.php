<?php

declare(strict_types=1);

namespace Kadath\Middlewares;

use Kadath\Adapters\IncrWithSupremumTtl;
use Kadath\Configuration\KarmaPolicy;
use Kadath\Exceptions\KarmaException;
use Lit\Middleware\IpAddress\IpAddress;
use Lit\Nimo\AbstractMiddleware;
use Predis\Client;
use Predis\Profile\RedisProfile;
use Psr\Http\Message\ResponseInterface;

class KarmaMiddleware extends AbstractMiddleware implements KarmaPolicy
{
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

    protected $type = self::KARMA_TYPE_ANONYMOUS;
    /**
     * @var Client
     */
    private $redisClient;

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

    public function commit(int $karma)
    {
        assert($karma > 0);
        $cmd = $this->redisClient->createCommand(IncrWithSupremumTtl::ID, [
            $this->makeKarmaKey(),
            $karma,
            self::KARMA_CAPABILITY[$this->type],
            self::KARMA_TTL,
        ]);

        $result = $this->redisClient->executeCommand($cmd);
        if ($result < 0) {
            throw KarmaException::confess();
        }

        return $result;
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

        $this->commit(self::KARMA_COST_GENERAL_REQUEST);
        return $this->delegate();
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
