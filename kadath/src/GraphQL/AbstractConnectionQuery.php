<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use Kadath\Database\AbstractRecord;
use Kadath\Database\AbstractRepository;
use Kadath\Exceptions\KadathException;
use Kadath\Pagination\PaginationArgument;

abstract class AbstractConnectionQuery extends AbstractKadathResolver implements \ArrayAccess
{
    /**
     * @var array
     * @see \Kadath\GraphQL\ResolveArrayAccessTrait
     */
    protected static $resolvedFields = [
        'edges' => true,
        'nodes' => true,
        'pageInfo' => true,
        'totalCount' => true,
    ];
    use ResolveArrayAccessTrait;
    protected $cursorCount;
    protected $cursorWhere;
    /**
     * @var PaginationArgument
     */
    protected $paginationArgument;
    protected $where;
    protected $order;
    protected $rows;
    protected $rowCount;

    public function resolve()
    {
        $pagination = PaginationArgument::fromArray($this->args['page']);
        $where = $this->resolveWhere($this->args);
        $order = $this->resolveOrder($this->args, $pagination);

        $this->prepare($this->getRepo(), $where, $order, $pagination);

        return $this;
    }

    protected function resolveTotalCount()
    {
        return $this->getRowCount();
    }


    protected function resolveNodes()
    {
        return $this->getRows();
    }

    protected function resolveEdges()
    {
        return array_map(function ($record) {
            return [
                'node' => $record,
                'cursor' => function () use ($record) {
                    $hash = self::queryHash($this->where, $this->order);
                    return $this->makeCursor($hash, $record);
                }
            ];
        }, $this->getRows());
    }

    protected function resolvePageInfo()
    {
        $hash = self::queryHash($this->where, $this->order);
        return [
            'startCursor' => function () use ($hash) {
                $rows = $this->getRows();
                if (empty($rows)) {
                    return null;
                }
                return $this->makeCursor($hash, $rows[0]);
            },
            'endCursor' => function () use ($hash) {
                $rows = $this->getRows();
                if (empty($rows)) {
                    return null;
                }
                return $this->makeCursor($hash, $rows[count($rows) - 1]);
            },
            'hasNextPage' => function () {
                return $this->paginationArgument->isForward()
                    ? $this->hasNotReachEnd()
                    : $this->hasLeaveStart();
            },
            'hasPreviousPage' => function () {
                return $this->paginationArgument->isForward()
                    ? $this->hasLeaveStart()
                    : $this->hasNotReachEnd();
            },
        ];
    }

    protected function hasNotReachEnd()
    {
        return $this->getCursorCount() > $this->paginationArgument->getSize();
    }

    protected function hasLeaveStart()
    {
        return $this->getRowCount() > $this->getCursorCount();
    }

    protected function getRowCount()
    {
        if (!isset($this->rowCount)) {
            $this->rowCount = $this->getRepo()->count($this->where);
        }

        return $this->rowCount;
    }

    protected function getRows()
    {
        if (!isset($this->rows)) {
            $query = $this->getRepo()->select($this->cursorWhere, $this->paginationArgument->getSize(), $this->order);
            $this->rows = $query->fetchAll();
        }
        return $this->rows;
    }

    abstract protected function getRepo(): AbstractRepository;

    protected function makeCursor(int $queryHash, AbstractRecord $record)
    {
        $repo = $this->getRepo();
        return $this->context->hashids->encode($queryHash, $record->{$repo::PK_FIELD});
    }

    protected function resolveWhere(array $args): array
    {
        return [];
    }

    protected function resolveOrder(array $args, PaginationArgument $paginationArgument): array
    {
        return [];
    }

    protected function prepare(
        AbstractRepository $repo,
        array $where,
        array $order,
        PaginationArgument $paginationArgument
    ): void
    {
        $cursor = $paginationArgument->getCursor();
        if (!empty($cursor)) {
            $hash = static::queryHash($where, $order);
            $cursor = $this->context->hashids->decode($cursor);
            if (empty($cursor) || $hash !== $cursor[0]) {
                throw KadathException::badRequest('bad cursor');
            }

            array_shift($cursor);
            $cursorWhere = $this->parseCursorWhere($cursor, $repo, $paginationArgument) + $where;
            $order = array_merge($order, $this->parseCursorOrder($cursor, $repo, $paginationArgument));
        } else {
            $cursorWhere = $where;
        }

        $this->paginationArgument = $paginationArgument;
        $this->where = $where;
        $this->cursorWhere = $cursorWhere;
        $this->order = $order;
    }

    protected function parseCursorWhere(array $cursor, AbstractRepository $repo, PaginationArgument $paginationArgument)
    {
        if (empty($cursor)) {
            throw KadathException::badRequest('bad cursor');
        }
        $method = $paginationArgument->isForward() ? 'gt' : 'lt';

        return [
            $repo::PK_FIELD => [$method => $cursor[0]],
        ];
    }

    protected function parseCursorOrder(array $cursor, AbstractRepository $repo, PaginationArgument $paginationArgument)
    {
        $order = $paginationArgument->isForward() ? 1 : -1;

        return [
            $repo::PK_FIELD => $order,
        ];
    }

    protected function getCursorCount()
    {
        if (!isset($this->cursorCount)) {
            $this->cursorCount = empty($this->paginationArgument->getCursor())
                ? $this->getRowCount()
                : $this->getRepo()->count($this->cursorWhere);
        }
        return $this->cursorCount;
    }

    /**
     * @param array $where
     * @param array $order
     * @return int
     */
    protected static function queryHash(array $where, array $order): int
    {
        ksort($where);
        return crc32(serialize([$where, $order])) % 0x1000000;
    }
}
