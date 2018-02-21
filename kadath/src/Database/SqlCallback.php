<?php

declare(strict_types=1);

namespace Kadath\Database;

interface SqlCallback
{
    public function __invoke(SqlBuilder $builder);
}
