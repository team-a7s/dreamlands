<?php

declare(strict_types=1);

namespace Kadath;

use Cache\Adapter\Apcu\ApcuCachePool;
use Dotenv\Dotenv;
use Kadath\Middlewares\KarmaMiddleware;
use Kadath\Middlewares\SessionMiddleware;
use Kadath\Middlewares\TransactionMiddleware;
use Lit\Air\Factory;
use Lit\Bolt\BoltApp;
use Lit\Bolt\BoltContainer;
use Lit\Bolt\BoltRouterApp;
use Lit\Middleware\IpAddress\IpAddress;
use Psr\Container\ContainerInterface;
use const Lit\Bolt\EVENT_AFTER_LOGIC;

class Kadath
{
    const ENV_SALT = 'SALT';
    const ENV_DATABASE_DSN = 'DATABASE_DSN';
    const ENV_CONTAINER_CLASS = 'CONTAINER_CLASS';
    const ENV_GITHUB_OAUTH = 'GITHUB_OAUTH';
    const ENV_REDIS_PARAM = 'REDIS_PARAM';
    const ENV_RECAPTCHA_SECRET = 'RECAPTCHA_SECRET';
    const ENV_RECAPTCHA_URL = 'RECAPTCHA_URL';


    public static function decorateApp(callable $delegate, BoltContainer $container, ?string $id = null)
    {
        /**
         * @var BoltApp $app
         */
        $app = $delegate($container, $id);
        $factory = Factory::of($container);

        $container->events->addListener(EVENT_AFTER_LOGIC, function () use ($factory) {
            $factory->getOrProduce(ApcuCachePool::class)->commit();
        });

        /** @noinspection PhpParamsInspection */
        $app->getMiddlewarePipe()
            ->append($factory->getOrProduce(TransactionMiddleware::class))
            ->append($factory->getOrProduce(IpAddress::class))
            ->append($factory->getOrProduce(SessionMiddleware::class))
            ->append($factory->getOrProduce(KarmaMiddleware::class));

        return $app;
    }

    public static function loadEnv()
    {
        $dotenv = new Dotenv(__DIR__ . '/../');
        $dotenv->overload();
        $dotenv->required([
            Kadath::ENV_CONTAINER_CLASS,
            Kadath::ENV_DATABASE_DSN,
            Kadath::ENV_REDIS_PARAM,
            Kadath::ENV_SALT,
            Kadath::ENV_GITHUB_OAUTH,
            Kadath::ENV_RECAPTCHA_SECRET,
        ]);
    }

    public static function isDebug()
    {
        return $_ENV['ENV'] ?? 'development' === 'development';
    }

    public static function makeContainer(array $config = []): BoltContainer
    {
        if (!isset($_ENV['CONTAINER_CLASS']) || !is_subclass_of($_ENV['CONTAINER_CLASS'], ContainerInterface::class)) {
            echo 'bad CONTAINER_CLASS: ' . $_ENV['CONTAINER_CLASS'], PHP_EOL;
            die(-1);
        }

        return new $_ENV['CONTAINER_CLASS']($config);
    }

    public static function run()
    {
        BoltRouterApp::run(self::makeContainer());
    }
}
