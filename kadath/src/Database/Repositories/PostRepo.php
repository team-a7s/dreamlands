<?php

declare(strict_types=1);

namespace Kadath\Database\Repositories;

use Kadath\Database\AbstractRepository;
use Kadath\Database\Records\PostRecord;

/**
 * Class PostRepo
 * @package Kadath\Repositories
 *
 * @method PostRecord find(string $id)
 */
class PostRepo extends AbstractRepository
{
    const RECORD_CLASS = PostRecord::class;
}
