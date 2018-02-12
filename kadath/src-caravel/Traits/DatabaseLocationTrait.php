<?php

declare(strict_types=1);

namespace Lit\Caravel\Traits;

use Doctrine\DBAL\DriverManager;

trait DatabaseLocationTrait
{
    protected $connection;

    protected function getConnection()
    {
        if (!isset($this->connection)) {

            $dsn = $this->getConfig('dsn');
            if (!$dsn) {
                throw new \InvalidArgumentException('empty dsn');
            }

            $this->connection = DriverManager::getConnection([
                'url' => $dsn,
            ]);
        }

        return $this->connection;
    }
}
