<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

trait ResolveArrayAccessTrait
{
    public function offsetExists($offset)
    {
        return isset(static::$resolvedFields[$offset]);
    }

    public function offsetGet($offset)
    {
        $method = 'resolve' . ucfirst($offset);
        return $this->$method();
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException();
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException();
    }

}
