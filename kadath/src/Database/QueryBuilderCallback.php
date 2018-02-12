<?php

declare(strict_types=1);

namespace Kadath\Database;

use Doctrine\DBAL\Query\QueryBuilder;

interface QueryBuilderCallback
{
    public function __invoke(QueryBuilder $queryBuilder);
}
