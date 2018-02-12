<?php

namespace Kadath\Database;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractRepository
{
    public const RECORD_CLASS = AbstractRecord::class;
    public const TABLE_NAME = '';
    public const PK_FIELD = 'id';

    /**
     * @var string[]
     * @see \Doctrine\DBAL\Query\Expression\ExpressionBuilder
     */
    protected static $whereSuffix = [
        ' null' => 'isNull',
        ' !null' => 'isNotNull',
        ' like' => 'like',
        ' !like' => 'notLike',
        ' in' => 'in',
        ' !in' => 'notIn',
        '>=' => 'gte',
        '<=' => 'lte',
        '!=' => 'neq',
        '<>' => 'neq',
        '=' => 'eq',
        '>' => 'gt',
        '<' => 'lt',
    ];
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param array|AbstractRecord $value
     * @return int
     */
    public function insert($value): int
    {
        if ($value instanceof AbstractRecord) {
            $valueArr = $value->toArray();
        } else {
            $valueArr = $value;
        }

        $qb = $this->qb()->insert($this->getQuotedTableName());
        $qb
            ->values(array_combine(
                array_keys($valueArr),
                array_map([$qb, 'createPositionalParameter'], $valueArr)
            ));

        $rowcnt = $qb->execute();

        if ($rowcnt > 0 && $value instanceof AbstractRecord) {
            $value->{static::PK_FIELD} = $this->connection->lastInsertId();
        }

        return $rowcnt;
    }

    /**
     * @param array|QueryBuilderCallback $where
     * @param int $limit
     * @param int $offset
     * @return Statement
     * @throws \Exception
     */
    public function select($where, int $limit, array $orders = [], int $offset = 0): Statement
    {
        $qb = $this->qb()->select('*');
        $this->resolveWhere($qb, $where);

        foreach ($orders as $fld => $order) {
            $qb->addOrderBy($fld, $order < 0 ? 'DESC' : 'ASC');
        }
        $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $statement = $qb->execute();
        $statement->setFetchMode(\PDO::FETCH_CLASS, static::RECORD_CLASS);

        return $statement;
    }

    /**
     * @param array|QueryBuilderCallback $where
     * @return mixed
     * @throws \Exception
     */
    public function selectFirst($where)
    {
        return $this->select($where, 1)->fetch() ?: null;
    }

    public function count($where): int
    {
        $qb = $this->qb()->select('COUNT(*)');
        $this->resolveWhere($qb, $where);

        return (int)$qb->execute()->fetchColumn();
    }

    /**
     * @param array $values
     * @param array|QueryBuilderCallback $where
     * @param int $limit
     * @return int
     * @throws \Exception
     */
    public function update(array $values, $where, int $limit = 1): int
    {
        $qb = $this->qb()->update($this->getQuotedTableName());
        foreach ($values as $fld => $value) {
            $qb->set($fld, $qb->createNamedParameter($value));
        }
        $this->resolveWhere($qb, $where);
        $qb->setMaxResults($limit);

        return $qb->execute();
    }

    /**
     * @param array|QueryBuilderCallback $where
     * @param int $limit
     * @return int
     * @throws \Exception
     */
    public function delete($where, int $limit = 1): int
    {
        $qb = $this->qb()
            ->delete($this->getQuotedTableName())
            ->setMaxResults($limit);
        $this->resolveWhere($qb, $where);

        return $qb->execute();
    }

    /**
     * @param $pkValue
     * @return AbstractRecord|null
     * @throws \Exception
     */
    public function find($pkValue)
    {
        return $this
            ->select([
                static::PK_FIELD => $pkValue
            ], 1)
            ->fetch() ?: null;
    }

    protected function qb(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()->from($this->getQuotedTableName());
    }

    /**
     * @param QueryBuilder $qb
     * @param $where
     * @throws \Exception
     */
    protected function resolveWhere(QueryBuilder $qb, $where)
    {
        if ($where instanceof QueryBuilderCallback) {
            $where->__invoke($qb);
            return;
        }

        if (is_array($where)) {
            $eb = $qb->expr();
            foreach ($where as $key => $value) {
                foreach (self::$whereSuffix as $suffix => $method) {
                    $len = strlen($suffix);
                    if (strlen($key) <= $len || substr($key, -$len) !== $suffix) {
                        continue;
                    }
                    $fld = trim(substr($key, 0, -$len));
                    $this->applyWhereCondition($qb, $method, $fld, $value);

                    continue 2;
                }

                if (!is_array($value)) {
                    $qb->andWhere($eb->eq(trim($key), $qb->createNamedParameter($value)));
                    continue;
                }

                foreach ($value as $method => $val) {
                    $this->applyWhereCondition($qb, $method, $key, $val);
                }
            }

            return;
        }

        throw new \Exception(__METHOD__ . '/' . __LINE__);
    }

    /**
     * @param QueryBuilder $qb
     * @param $method
     * @param $fld
     * @param $value
     */
    protected function applyWhereCondition(QueryBuilder $qb, $method, $fld, $value): void
    {
        $expr = $qb->expr();
        if ($method === 'isNotNull' || $method === 'isNull') {
            $qb->andWhere($expr->{$method}(
                $fld
            ));
        } elseif ($method === 'in' || $method === 'notIn') {
            if (!empty($value)) {
                $qb->andWhere($expr->{$method}(
                    $fld,
                    array_map([$qb, 'createNamedParameter'], $value)
                ));
            } else {
                $qb->andWhere($method === 'in' ? '1=0' : '1=1');
            }
        } else {
            $qb->andWhere($expr->{$method}(
                $fld,
                $qb->createNamedParameter($value)
            ));
        }
    }

    /**
     * @return string
     */
    protected function getQuotedTableName(): string
    {
        if (empty(static::TABLE_NAME)) {
            preg_match('/.+\\\\([^\\\\]+)Record/', static::RECORD_CLASS, $match);
            $tableName = Inflector::tableize($match[1]);
        } else {
            $tableName = static::TABLE_NAME;
        }
        return $this->connection->quoteIdentifier($tableName);
    }
}