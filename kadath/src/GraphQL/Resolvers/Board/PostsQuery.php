<?php

declare(strict_types=1);


namespace Kadath\GraphQL\Resolvers\Board;


use Kadath\Database\Records\PostRecord;

class PostsQuery extends AbstractPostsByParentQuery
{
    const POST_TYPE = PostRecord::POST_TYPE_POST;


    public function injectParent(PostRecord $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    protected function isAsc()
    {
        return !($this->args['reversed'] ?? false);
    }
}