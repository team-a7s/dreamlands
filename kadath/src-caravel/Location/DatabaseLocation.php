<?php

declare(strict_types=1);

namespace Lit\Caravel\Location;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Lit\Caravel\Traits\DatabaseLocationTrait;

class DatabaseLocation extends AbstractLocation
{
    use DatabaseLocationTrait;

    public function read(): Schema
    {
        return $this->getConnection()->getSchemaManager()->createSchema();
    }

    public function update(SchemaDiff $diff)
    {
        $connection = $this->getConnection();

        $sqls = $diff->toSaveSql($connection->getDatabasePlatform());
        foreach ($sqls as $sql) {
            $connection->exec($sql);
        }
    }
}
