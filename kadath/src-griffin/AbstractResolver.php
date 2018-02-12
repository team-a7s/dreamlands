<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Type\Definition\ResolveInfo;
use Lit\Air\Injection\SetterInjector;

abstract class AbstractResolver
{
    const SETTER_INJECTOR = SetterInjector::class;

    protected $source;
    /**
     * @var array
     */
    protected $args;
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var ResolveInfo
     */
    protected $resolveInfo;

    public function __construct($source, array $args, Context $context, ResolveInfo $resolveInfo)
    {
        $this->source = $source;
        $this->args = $args;
        $this->context = $context;
        $this->resolveInfo = $resolveInfo;
    }

    abstract public function resolve();

    public static function getResolver(array $extra = [])
    {
        return function ($source, array $args, Context $context, ResolveInfo $resolveInfo) use ($extra) {
            return $context->resolve(static::class, $source, $args, $resolveInfo, $extra);
        };
    }

    protected function export(ExportableInterface $target)
    {
        return $this->context->objectRepository->export($target);
    }

    protected function create(string $type, array $data = [])
    {
        return $this->context->objectRepository->create($type, $data);
    }
}
