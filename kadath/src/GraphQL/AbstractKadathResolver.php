<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Kadath\Middlewares\KarmaMiddleware;
use Lit\Griffin\AbstractResolver;
use Lit\Griffin\Context;

/**
 * Class AbstractKadathResolver
 * @package Kadath
 *
 * @property KadathContext $context
 */
abstract class AbstractKadathResolver extends AbstractResolver
{
    const KARMA_COST = KarmaMiddleware::KARMA_COST_GENERAL_REQUEST;

    public function __construct($source, array $args, Context $context, ResolveInfo $resolveInfo)
    {
        parent::__construct($source, $args, $context, $resolveInfo);
        $this->beforeResolve();
    }

    protected function beforeResolve()
    {
        $this->context->karma()->commit(static::KARMA_COST);
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
