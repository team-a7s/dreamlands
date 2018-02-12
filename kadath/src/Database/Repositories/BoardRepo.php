<?php

declare(strict_types=1);

namespace Kadath\Database\Repositories;

use Kadath\Database\AbstractRepository;
use Kadath\Database\Records\BoardRecord;

/**
 * Class BoardRepo
 * @package Kadath\Repositories
 *
 * @method BoardRecord find(string $id)
 */
class BoardRepo extends AbstractRepository
{
    const RECORD_CLASS = BoardRecord::class;
}
