<?php

declare(strict_types=1);

namespace Lit\Griffin;

interface ExportableInterface
{
    public function exportArray(): array;

    public function getObjectType(): string;
}
