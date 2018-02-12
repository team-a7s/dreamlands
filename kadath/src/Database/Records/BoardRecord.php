<?php

declare(strict_types=1);

namespace Kadath\Database\Records;

use Kadath\Database\AbstractRecord;
use Kadath\Database\TableBoard;
use Kadath\GraphQL\NodeIdentify;
use Kadath\GraphQL\Resolvers\Board\ThreadsQuery;
use Lit\Griffin\ExportableInterface;
use Lit\Griffin\ExportableTrait;

class BoardRecord extends AbstractRecord implements TableBoard, ExportableInterface
{
    use ExportableTrait;
    const OBJECT_TYPE = 'Board';
    const NODE_TYPE = NodeIdentify::TYPE_BOARD;

    public function exportArray(): array
    {
        return [
            'id' => $this->idResolver(),
            'name' => $this->name,
            'tagline' => $this->tagline,
            'threads' => $this->delegateResolver(ThreadsQuery::class),
        ];
    }
}
