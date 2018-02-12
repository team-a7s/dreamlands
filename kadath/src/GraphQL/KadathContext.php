<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use Hashids\Hashids;
use Kadath\Database\AbstractRecord;
use Kadath\Database\AbstractRepository;
use Kadath\Exceptions\KadathException;
use Kadath\Middlewares\SessionMiddleware;
use Lit\Griffin\Context;
use Lit\Middleware\IpAddress\IpAddress;
use Lit\Nexus\Interfaces\KeyValueInterface;

class KadathContext extends Context
{
    /**
     * @var Hashids
     */
    public $hashids;

    /**
     * @var NodeIdentify
     */
    public $nodeIdentify;

    public function injectNodeIdentify(NodeIdentify $nodeIdentify)
    {
        $this->nodeIdentify = $nodeIdentify;
        $this->hashids = $nodeIdentify->getHashids();
        return $this;
    }

    public function session(): SessionMiddleware
    {
        return SessionMiddleware::fromRequest($this->request);
    }

    public function sessionStorage(): KeyValueInterface
    {
        $session = SessionMiddleware::fromRequest($this->request)->getSession();
        if (!$session) {
            throw KadathException::invalidSession();
        }

        return $session;
    }

    public function ipAddress(): IpAddress
    {
        return IpAddress::fromRequest($this->request);
    }

    public function fetchNode(int $type, $id): ?AbstractRecord
    {
        [$repoClass] = NodeIdentify::$meta[$type];
        /** @var AbstractRepository $repo */
        $repo = $this->factory->getOrProduce($repoClass);
        return $repo->find($id);
    }
}
