<?php

declare(strict_types=1);

namespace Lit\Griffin;

trait ExportableTrait
{
    public function getObjectType(): string
    {
        /** @noinspection PhpUndefinedClassConstantInspection */
        return static::OBJECT_TYPE;
    }
}
