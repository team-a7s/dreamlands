<?php

declare(strict_types=1);


namespace Kadath\Adapters;


use Doctrine\DBAL\Logging\SQLLogger;
use Monolog\Logger;

class KadathLogger extends Logger implements SQLLogger
{
    private static $instance;
    private $queryStart;

    private $queryData;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self('default');
        }

        return self::$instance;
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queryData = get_defined_vars();
        $this->queryStart = microtime(true);
    }

    public function stopQuery()
    {
        $this->info('SQL: ' . $this->queryData['sql'],
            [
                'cost' => round(1000 * (microtime(true) - $this->queryStart), 2),
            ] + $this->queryData
        );
    }
}