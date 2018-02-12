<?php

declare(strict_types=1);

namespace Lit\Griffin;

interface ObjectRepositoryInterface
{
    public function create(string $type, array $data = []);

    public function export(ExportableInterface $target);
}