<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Board;

use Kadath\Database\AbstractRecord;
use Kadath\Database\AbstractRepository;
use Kadath\Database\Records\BoardRecord;
use Kadath\Database\Records\PostRecord;
use Kadath\Database\Repositories\PostRepo;
use Kadath\Database\SqlBuilder;
use Kadath\Database\SqlCallback;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractConnectionQuery;
use Kadath\Pagination\PaginationArgument;
use Lit\Nimo\Tests\RememberConstructorParamTrait;

abstract class AbstractPostsByParentQuery extends AbstractConnectionQuery
{
    protected const IS_ASC = false;
    protected const POST_TYPE = -1;
    /**
     * @var PostRepo
     */
    protected $postRepo;
    /**
     * @var BoardRecord|PostRecord
     */
    protected $parent;

    public function injectPostRepo(PostRepo $postRepo)
    {
        $this->postRepo = $postRepo;
        return $this;
    }

    protected function getRepo(): AbstractRepository
    {
        return $this->postRepo;
    }

    protected function resolveWhere(array $args): array
    {
        return [
            'type' => static::POST_TYPE,
            'parent_id' => $this->parent->id,
            'deleted_at' => 0,
        ];
    }

    /**
     * @param int $queryHash
     * @param PostRecord $record
     * @return string
     */
    protected function makeCursor(int $queryHash, AbstractRecord $record)
    {
        return $this->context->hashids->encode($queryHash, $record->touched_at, $record->id);
    }


    protected function parseCursorWhere(array $cursor, AbstractRepository $repo, PaginationArgument $paginationArgument)
    {
        if (empty($cursor)) {
            throw KadathException::badRequest('bad cursor');
        }
        $method = ($paginationArgument->isForward() ^ self::IS_ASC) ? 'lt' : 'gt';

        return [
            new class($cursor, $method) implements SqlCallback
            {
                use RememberConstructorParamTrait;

                public function __invoke(SqlBuilder $builder)
                {
                    [[$touchedAt, $id], $method] = $this->params;
                    $expr = $builder->expr();
                    $touchedParam = $builder->namedParam($touchedAt);

                    return $expr->orX(
                        $expr->andX(
                            $expr->eq('touched_at', $touchedParam),
                            $expr->{$method}('id', $builder->namedParam($id))
                        ),
                        $expr->{$method}('touched_at', $touchedParam)
                    );
                }
            }
        ];
    }

    protected function resolveOrder(array $args, PaginationArgument $paginationArgument): array
    {
        $order = ($paginationArgument->isForward() ^ static::IS_ASC) ? -1 : 1;

        return [
            'touched_at' => $order,
            'id' => $order,
        ];
    }
}
