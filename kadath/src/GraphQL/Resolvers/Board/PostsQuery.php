<?php

declare(strict_types=1);


namespace Kadath\GraphQL\Resolvers\Board;


use Kadath\Database\Records\PostRecord;

class PostsQuery extends AbstractPostsByParentQuery
{
    const POST_TYPE = PostRecord::POST_TYPE_POST;
    const IS_ASC = true;

    public function injectParent(PostRecord $parent)
    {
        $this->parent = $parent;
        return $this;
    }
}