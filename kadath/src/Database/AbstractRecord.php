<?php

declare(strict_types=1);

namespace Kadath\Database;

use GraphQL\Type\Definition\ResolveInfo;
use Kadath\GraphQL\KadathContext;
use Kadath\Utility\Utility;

abstract class AbstractRecord
{
    public function toArray()
    {
        return Utility::getObjectVars($this);
    }

    public function idResolver()
    {
        return function ($src, array $args, KadathContext $context, ResolveInfo $resolveInfo) {
            return $context->nodeIdentify->getId($this);
        };
    }

    public function delegateResolver(string $class)
    {
        return function ($src, array $args, KadathContext $context, ResolveInfo $resolveInfo) use ($class) {
            return $context->resolve($class, $src, $args, $resolveInfo, [
                static::class => $this
            ]);
        };
    }
}
