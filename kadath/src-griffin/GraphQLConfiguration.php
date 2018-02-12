<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Error\Debug;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Lit\Air\Configurator;
use Lit\Bolt\BoltApp;

class GraphQLConfiguration
{
    public const SERVER_CONFIG = self::class . '::SERVER_CONFIG';

    public static function default()
    {
        return [
            BoltApp::MAIN_HANDLER => Configurator::produce(GraphQLAction::class),
            StandardServer::class => Configurator::provideParameter([
                'config' => Configurator::alias(ServerConfig::class),
            ]),
            ServerConfig::class => function (Schema $schema, GlobalFieldResolver $fieldResolver, Context $context) {
                return ServerConfig::create()
                    ->setSchema($schema)
                    ->setFieldResolver($fieldResolver)
                    ->setContext($context)
                    ->setDebug(Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE);
            },
            Schema::class => function (SourceBuilder $sourceBuilder, TypeConfigDecorator $typeConfigDecorator) {
                return BuildSchema::build($sourceBuilder->build(), $typeConfigDecorator);
            },
            ObjectRepositoryInterface::class => Configurator::produce(ObjectRepository::class),
        ];
    }
}
