<?php

declare(strict_types=1);

namespace Kadath;

use Cache\Adapter\Apcu\ApcuCachePool;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Hashids\Hashids;
use Kadath\Action\NotFoundAction;
use Kadath\Adapters\KLogger;
use Kadath\Adapters\RedisKeyValue;
use Kadath\Adapters\RouteDefinition;
use Kadath\Adapters\RouterStubResolver;
use Kadath\Database\AbstractRepository;
use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\KadathObjectRepository;
use Kadath\GraphQL\NodeIdentify;
use Kadath\Middlewares\SessionMiddleware;
use Kadath\Utility\IdGenerator;
use Kadath\Utility\IdGeneratorInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\OAuth2\Client\Provider\Github;
use Lit\Air\Configurator as C;
use Lit\Air\Factory;
use Lit\Bolt\BoltContainer;
use Lit\Bolt\BoltRouterApp;
use Lit\Core\Interfaces\RouterInterface;
use Lit\Griffin\Context;
use Lit\Griffin\GraphQLConfiguration;
use Lit\Griffin\ObjectClassGenerator;
use Lit\Griffin\ObjectRepositoryInterface;
use Lit\Griffin\SourceBuilder;
use Lit\Nexus\Cache\CacheKeyValue;
use Lit\Nexus\Derived\PrefixKeyValue;
use Lit\Nexus\Interfaces\KeyValueInterface;
use Lit\Nexus\Traits\KeyValueTrait;
use Lit\Router\FastRoute\CachedDispatcher;
use Lit\Router\FastRoute\FastRouteRouter;
use Predis\Client as RedisClient;
use Psr\Log\LoggerInterface;

/**
 * Class KadathContainer
 * @package Kadath
 *
 * @property KeyValueInterface|KeyValueTrait $memoryCache
 */
class KadathContainer extends BoltContainer
{
    const MEMORY_CACHE = 'memoryCache';

    public function __construct(array $config = null)
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $defaultConfiguration = [
                //Bolt
                BoltContainer::class => $this,
                BoltRouterApp::class => C::decorateCallback(
                    C::provideParameter([]),
                    [Kadath::class, 'decorateApp']
                ),
                RouterInterface::class => C::instance(FastRouteRouter::class, [
                    C::instance(CachedDispatcher::class, [
                        function () {
                            return $this->memoryCache->slice('route');
                        },
                        C::instance(\FastRoute\RouteParser\Std::class),
                        C::instance(\FastRoute\DataGenerator\GroupCountBased::class),
                        C::instance(RouteDefinition::class),
                        \FastRoute\Dispatcher\GroupCountBased::class,
                    ]),
                    C::instance(RouterStubResolver::class),
                    [NotFoundAction::class],
                ]),

                //Kadath
                SessionMiddleware::class => C::provideParameter([
                    'storage' => $this->redisCache(86400, 'session:'),
                ]),
                RedisClient::class => C::provideParameter(json_decode($_ENV[Kadath::ENV_REDIS_PARAM], true)),
                Connection::class => function () {
                    $connection = DriverManager::getConnection([
                        'url' => $_ENV[Kadath::ENV_DATABASE_DSN],
                    ]);

                    $connection->getConfiguration()->setSQLLogger(KLogger::instance());
                    return $connection;
                },
                NodeIdentify::class => C::provideParameter([
                    Hashids::class => C::singleton(Hashids::class, [
                        hash_hmac('sha1', $_ENV[Kadath::ENV_SALT], 'node_id'),//salt
                        6,//minLength
                    ])
                ]),
                IdGeneratorInterface::class => C::singleton(IdGenerator::class),
                Github::class => C::provideParameter([
                    function () {
                        parse_str($_ENV[Kadath::ENV_GITHUB_OAUTH], $option);

                        return $option;
                    }
                ]),
                self::MEMORY_CACHE => function (ApcuCachePool $pool) {
                    return new CacheKeyValue($pool);
                },
                LoggerInterface::class => function () {
                    return KLogger::instance();
                },
                AbstractRepository::class . '::' => [ //inject to all child class
                    'cache' => $this->redisCache(3600, 'db:'),
                ],

                //Griffin
                Context::class => C::produce(KadathContext::class),
                ObjectRepositoryInterface::class => C::produce(KadathObjectRepository::class),
                ObjectClassGenerator::class => C::provideParameter([
                    FilesystemInterface::class =>
                        C::singleton(Filesystem::class, [
                            C::singleton(Local::class, [__DIR__ . '/GraphQL/Type']),
                        ]),
                    'namespace' => KadathObjectRepository::TYPE_NAMESPACE,
                ]),
                SourceBuilder::class => C::provideParameter([
                    'cache' => function () {
                        return $this->memoryCache->slice('graphql_source');
                    },
                    'path' => __DIR__ . '/schema.graphqls',
                ]),
            ] + GraphQLConfiguration::default();


        parent::__construct(($config ?: []) + $defaultConfiguration);
    }

    protected function redisCache($expire, $prefix)
    {
        return function () use ($prefix, $expire) {
            /** @noinspection PhpParamsInspection */
            return PrefixKeyValue::wrap(
                Factory::of($this)->instantiate(RedisKeyValue::class, ['expire' => $expire]),
                $prefix
            );
        };
    }
}
