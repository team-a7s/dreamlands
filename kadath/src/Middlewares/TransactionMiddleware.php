<?php

declare(strict_types=1);


namespace Kadath\Middlewares;


use Doctrine\DBAL\Connection;
use Lit\Nimo\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface;

class TransactionMiddleware extends AbstractMiddleware
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    protected function main(): ResponseInterface
    {
        try {
            $result = $this->delegate();

            while ($this->connection->isTransactionActive()) {
                $this->connection->commit();
            }

            return $result;
        } catch (\Throwable $exception) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }
}