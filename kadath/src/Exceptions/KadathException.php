<?php

declare(strict_types=1);

namespace Kadath\Exceptions;

use GraphQL\Error\ClientAware;
use Throwable;

class KadathException extends \Exception implements ClientAware
{
    protected $category = 'logic';

    public function __construct(string $message = "", string $category = "logic", ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->category = $category;
    }


    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public static function invalidSession(?Throwable $previous = null)
    {
        return new static('invalid session', 'session', $previous);
    }

    public static function auth($msg, ?Throwable $previous = null)
    {
        return new static($msg, 'auth', $previous);
    }

    public static function badRequest($msg, ?Throwable $previous = null)
    {
        return new static($msg, 'request', $previous);
    }
}
