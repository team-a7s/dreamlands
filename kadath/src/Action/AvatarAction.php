<?php

declare(strict_types=1);


namespace Kadath\Action;


use Identicon\Identicon;
use Kadath\Adapters\KadataAction;
use Kadath\Database\Records\UserRecord;
use Kadath\Exceptions\HttpException;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\NodeIdentify;
use Lit\Air\Injection\SetterInjector;
use Lit\Nimo\MiddlewarePipe;
use Middlewares\Expires;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AvatarAction extends KadataAction
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
    protected $context;

    public function injectContext(KadathContext $kadathContext)
    {
        $this->context = $kadathContext;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->context->request = $request;
        return parent::handle($request);
    }


    protected function main(): ResponseInterface
    {
        $nodeId = $this->request->getAttribute('nodeId');
        try {
            [$type, $id] = $this->context->nodeIdentify->decodeId($nodeId);
        } catch (KadathException $e) {
            throw HttpException::notFound();
        }
        if ($type != NodeIdentify::TYPE_USER) {
            throw HttpException::notFound();
        }
        /**
         * @var UserRecord $user
         */
        $user = $this->context->fetchNode($type, $id);
        if (!$user) {
            throw HttpException::notFound();
        }

        $data = $this->identicon->getImageData($user->uniq . $nodeId);
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($data);

        return $response
            ->withHeader('Content-Type', 'image/png');
    }

    public static function prependMiddleware(MiddlewarePipe $pipe)
    {
        $pipe->append((new Expires())->defaultExpires('+30 day'));
    }
}
