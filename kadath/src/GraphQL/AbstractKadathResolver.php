<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use Lit\Griffin\AbstractResolver;

/**
 * Class AbstractKadathResolver
 * @package Kadath
 *
 * @property KadathContext $context
 */
abstract class AbstractKadathResolver extends AbstractResolver
{

    /**
     * wrapper for wrap resolver in closure for GlobalFieldResolver
     * @param callable $resolver
     * @return \Closure
     */
    protected static function resolver(callable $resolver)
    {
        return function (...$args) use ($resolver) {
            return $resolver(...$args);
        };
    }

}
