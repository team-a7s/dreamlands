<?php

declare(strict_types=1);


namespace Kadath\Action;


use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\NodeIdentify;
use Lit\Bolt\BoltAction;
use Psr\Http\Message\ResponseInterface;

class NotFoundAction extends BoltAction
{
    protected function main(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(404);
        $response->getBody()->write('not found');

        return $response;
    }
}