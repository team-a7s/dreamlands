<?php

declare(strict_types=1);

namespace Kadath\Adapters;

use Lit\Nexus\Interfaces\KeyValueInterface;
use Lit\Nexus\Traits\KeyValueTrait;
use Predis\Client;

class RedisKeyValue implements KeyValueInterface
{
    use KeyValueTrait;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var int
     */
    private $expire;

    public function __construct(Client $client, int $expire)
    {
        $this->client = $client;
        $this->expire = $expire;
    }

    public function set($key, $value)
    {
        return $this->client->setex($key, $this->expire, $value);
    }

    public function delete($key)
    {
        return $this->client->del([$key]);
    }

    public function get($key)
    {
        return $this->client->get($key);
    }

    public function exists($key): bool
    {
        return !!$this->client->exists($key);
    }

    protected function getPrefixDelimiter()
    {
        return ':';
    }
}
