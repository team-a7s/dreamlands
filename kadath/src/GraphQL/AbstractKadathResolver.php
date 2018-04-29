<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use Kadath\Middlewares\KarmaMiddleware;
use Lit\Griffin\AbstractResolver;

/**
 * Class AbstractKadathResolver
 * @package Kadath
 *
 * @property KadathContext $context
 */
abstract class AbstractKadathResolver extends AbstractResolver
{
    const KARMA_COST = KarmaMiddleware::KARMA_COST_GENERAL_REQUEST;

    public function resolve()
    {
        $this->beforeResolve();
        return $this->doResolve();
    }

    abstract public function doResolve();

    protected function beforeResolve()
    {
        if (static::KARMA_COST > 0) {
            $this->context->karma()->commit(static::KARMA_COST);
        }
    }

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
