<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Board;

use Kadath\Database\AbstractRepository;
use Kadath\Database\Repositories\BoardRepo;
use Kadath\GraphQL\AbstractConnectionQuery;

class BoardsQuery extends AbstractConnectionQuery
{
    /**
     * @var BoardRepo
     */
    protected $boardRepo;

    public function injectBoardRepo(BoardRepo $boardRepo)
    {
        $this->boardRepo = $boardRepo;
        return $this;
    }

    protected function getRepo(): AbstractRepository
    {
        return $this->boardRepo;
    }
}
