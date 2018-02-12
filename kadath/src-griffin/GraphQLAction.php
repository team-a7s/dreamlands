<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Server\StandardServer;
use Lit\Bolt\BoltAction;
use Psr\Http\Message\ResponseInterface;

class GraphQLAction extends BoltAction
{
    /**
     * @var StandardServer
     */
    protected $server;
    /**
     * @var Context
     */
    protected $context;

    public function __construct(StandardServer $server, Context $context)
    {
        $this->server = $server;
        $this->context = $context;
    }

    protected function main(): ResponseInterface
    {
        $this->context->request = $this->request;
        $result = $this->server->executePsrRequest($this->request);

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write(json_encode($result));
        
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
