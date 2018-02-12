<?php

declare(strict_types=1);

namespace Lit\Caravel\Location;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Lit\Caravel\Traits\DatabaseLocationTrait;

class SqlLocation extends AbstractLocation
{
    use DatabaseLocationTrait;

    public function read(): Schema
    {
        throw new \Exception('not implemented');
    }

    public function update(SchemaDiff $diff)
    {
        $fp = fopen($this->getConfig('output', 'php://stdout'), 'w');
        $sqls = $diff->toSaveSql($this->getConnection()->getDatabasePlatform());
        fwrite($fp, implode("\n\n", $sqls));
        fclose($fp);
    }

    public function write(Schema $schema)
    {
        $fp = fopen($this->getConfig('output', 'php://stdout'), 'w');
        $sqls = $schema->toSql($this->getConnection()->getDatabasePlatform());
        fwrite($fp, implode("\n\n", $sqls));
        fclose($fp);
    }

}
