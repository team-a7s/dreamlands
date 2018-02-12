<?php

declare(strict_types=1);

namespace Kadath\Utility;

interface IdGeneratorInterface
{
    public function generate(): string;
}
