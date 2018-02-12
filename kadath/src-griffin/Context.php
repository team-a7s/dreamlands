<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Type\Definition\ResolveInfo;
use Lit\Air\Factory;
use Lit\Air\Injection\SetterInjector;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Context
 * @package Lit\Griffin
 * @property-read ObjectRepositoryInterface $objectRepository
 */
class Context
{
    const SETTER_INJECTOR = SetterInjector::class;

    /**
     * @var ServerRequestInterface
     */
    public $request;

    /**
     * @var ObjectRepositoryInterface
     */
    protected $objectRepository;
    /**
     * @var Factory
     */
    protected $factory;

    public function injectFactory(Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    public function injectObjectRepository(ObjectRepositoryInterface $objectRepository)
    {
        $this->objectRepository = $objectRepository;
        return $this;
    }

    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    public function resolve(string $resolverClass, $source, array $args, ResolveInfo $resolveInfo, array $extra = [])
    {
        /** @var AbstractResolver $resolver */
        $resolver = $this->factory->instantiate($resolverClass, $extra + [
            'source' => $source,
            'args' => $args,
            'resolveInfo' => $resolveInfo,
            'context' => $this,
        ]);

        return $resolver->resolve();
    }
}
