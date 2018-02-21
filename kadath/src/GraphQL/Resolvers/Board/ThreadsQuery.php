<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Board;

use Kadath\Database\Records\BoardRecord;
use Kadath\Database\Records\PostRecord;

class ThreadsQuery extends AbstractPostsByParentQuery
{
    const POST_TYPE = PostRecord::POST_TYPE_THREAD;

    public function injectParent(BoardRecord $parent)
    {
        $this->parent = $parent;
        return $this;
    }
}
