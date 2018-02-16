<?php

declare(strict_types=1);


namespace Kadath\Exceptions;


use Lit\Core\ThrowableResponse;
use Zend\Diactoros\Response;

class HttpException extends ThrowableResponse
{
    public static function notFound(string $body = '')
    {
        $response = new Response();
        $response->getBody()->write($body);

        return new self($response->withStatus(404));
    }
}