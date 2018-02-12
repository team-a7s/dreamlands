<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Model;

use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractKadathModel;
use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\Resolvers\Board\BoardsQuery;
use Kadath\GraphQL\Resolvers\Session\CurrentSessionQuery;

class QueryModel extends AbstractKadathModel
{
    protected static $resolverMap = [
        'session' => CurrentSessionQuery::class,
        'boards' => BoardsQuery::class,
    ];

    protected static $resolvedFields = [
        'node',
    ];

    /**
     * @param $source
     * @param array $args
     * @param KadathContext $context
     * @return object
     * @throws KadathException
     */
    public function resolveNode($source, array $args, KadathContext $context)
    {
        assert(isset($args['id']));

        [$type, $id] = $context->nodeIdentify->decodeId($args['id']);

        return $context->fetchNode($type, $id);
    }
}