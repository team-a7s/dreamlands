<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Board;

use Kadath\Database\AbstractRepository;
use Kadath\Database\Records\BoardRecord;
use Kadath\Database\Records\PostRecord;
use Kadath\Database\Repositories\PostRepo;
use Kadath\GraphQL\AbstractConnectionQuery;
use Kadath\Pagination\PaginationArgument;

class ThreadsQuery extends AbstractConnectionQuery
{
    /**
     * @var PostRepo
     */
    protected $postRepo;
    /**
     * @var BoardRecord
     */
    protected $parent;

    public function injectPostRepo(PostRepo $postRepo)
    {
        $this->postRepo = $postRepo;
        return $this;
    }

    public function injectParent(BoardRecord $parent)
    {
        $this->parent = $parent;
        return $this;
    }


    protected function getRepo(): AbstractRepository
    {
        return $this->postRepo;
    }

    protected function resolveWhere(array $args): array
    {
        return [
            'type' => PostRecord::POST_TYPE_THREAD,
            'parent_id' => $this->parent->id,
        ];
    }

    protected function resolveOrder(array $args, PaginationArgument $paginationArgument): array
    {
        return ['touched_at' => $paginationArgument->isForward() ? -1 : 1];
    }
}
