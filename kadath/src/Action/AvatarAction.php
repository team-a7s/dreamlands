<?php

declare(strict_types=1);


namespace Kadath\Action;


use Identicon\Identicon;
use Kadath\Database\Records\UserRecord;
use Kadath\Exceptions\HttpException;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\NodeIdentify;
use Lit\Air\Injection\SetterInjector;
use Lit\Bolt\BoltAction;
use Lit\Nimo\MiddlewarePipe;
use Middlewares\Expires;
use Psr\Http\Message\ResponseInterface;

class AvatarAction extends BoltAction
{
    const SETTER_INJECTOR = SetterInjector::class;
    /**
     * @var Identicon
     */
    protected $identicon;

    public function injectIdenticon(Identicon $identicon)
    {
        $this->identicon = $identicon;
        return $this;
    }

    /**
     * @var KadathContext
     */
    protected $kadathContext;

    public function injectKadathContext(KadathContext $kadathContext)
    {
        $this->kadathContext = $kadathContext;
        return $this;
    }

    protected function main(): ResponseInterface
    {
        $nodeId = $this->request->getAttribute('nodeId');
        try {
            [$type, $id] = $this->kadathContext->nodeIdentify->decodeId($nodeId);
        } catch (KadathException $e) {
            throw HttpException::notFound();
        }
        if ($type != NodeIdentify::TYPE_USER) {
            throw HttpException::notFound();
        }
        /**
         * @var UserRecord $user
         */
        $user = $this->kadathContext->fetchNode($type, $id);
        if (!$user) {
            throw HttpException::notFound();
        }

        $data = $this->identicon->getImageData($user->uniq . $nodeId);
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($data);

        return $response
            ->withHeader('Content-Type', 'image/png');
    }

    public static function routeHandler(self $action)
    {
        return $action->includeMiddleware((new MiddlewarePipe())
            ->append((new Expires())->defaultExpires('+30 day'))
        );
    }
}
