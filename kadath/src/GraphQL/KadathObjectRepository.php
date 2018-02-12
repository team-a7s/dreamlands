<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use Lit\Griffin\ObjectRepository;

class KadathObjectRepository extends ObjectRepository
{
    public const TYPE_NAMESPACE = 'Kadath\\GraphQL\\Type';
    public const MODEL_NAMESPACE = 'Kadath\\GraphQL\\Model';
}
