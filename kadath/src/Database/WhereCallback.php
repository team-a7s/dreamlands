<?php

declare(strict_types=1);

namespace Kadath\Database;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;

interface WhereCallback
{
    public function __invoke(WhereBuilder $builder);
}
