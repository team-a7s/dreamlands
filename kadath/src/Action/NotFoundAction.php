<?php

declare(strict_types=1);


namespace Kadath\Action;


use Kadath\Adapters\KadataAction;
use Psr\Http\Message\ResponseInterface;

class NotFoundAction extends KadataAction
{
    protected function main(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(404);
        $response->getBody()->write('not found');

        return $response;
    }
}