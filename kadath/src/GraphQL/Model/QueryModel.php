<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Model;

use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractKadathModel;
use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\Resolvers\Board\BoardsQuery;
use Kadath\GraphQL\Resolvers\Session\CurrentSessionQuery;
use Kadath\Middlewares\KarmaMiddleware;

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

        $context->karma()->commit(KarmaMiddleware::KARMA_COST_GENERAL_REQUEST);
        return $context->fetchNode($type, $id);
    }
}