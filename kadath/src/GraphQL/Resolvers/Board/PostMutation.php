<?php

declare(strict_types=1);

namespace Kadath\GraphQL\Resolvers\Board;

use Kadath\Database\Records\BoardRecord;
use Kadath\Database\Records\PostRecord;
use Kadath\Database\Repositories\PostRepo;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\AbstractKadathResolver;
use Kadath\GraphQL\NodeIdentify;
use Kadath\GraphQL\Type\PostTypeEnum;
use Kadath\Utility\Utility;

class PostMutation extends AbstractKadathResolver
{

    /**
     * @var PostRepo
     */
    protected $postRepo;

    public function injectPostRepo(PostRepo $postRepo)
    {
        $this->postRepo = $postRepo;
        return $this;
    }

    public function resolve()
    {
        $this->context->session()->needLogin();

        switch ($this->args['type']) {
            case PostTypeEnum::THREAD:
                return $this->resolvePostThread();
            case PostTypeEnum::POST:
                return $this->resolvePostPost();
            default:
                throw KadathException::badRequest('bad post type');
        }
    }

    public function resolvePostPost()
    {
        $threadId = $this->args['parentId'];
        [$type, $id] = $this->context->nodeIdentify->decodeId($threadId);
        if ($type !== NodeIdentify::TYPE_POST) {
            throw KadathException::badRequest('thread not found');
        }

        $thread = $this->context->fetchNode($type, $id);
        if (!$thread || !$thread instanceof PostRecord) {
            throw KadathException::badRequest('thread not found');
        }
        if ($thread->type !== PostRecord::POST_TYPE_THREAD) {
            throw KadathException::badRequest('thread not found');
        }

        $post = $this->populatePostRecord();
        $post->type = PostRecord::POST_TYPE_POST;
        $post->parent_id = $thread->id;

        $post->validate();
        $this->postRepo->insert($post);

        return $post;
    }

    public function resolvePostThread()
    {
        $boardId = $this->args['parentId'];
        [$type, $id] = $this->context->nodeIdentify->decodeId($boardId);
        if ($type !== NodeIdentify::TYPE_BOARD) {
            throw KadathException::badRequest('board not found');
        }

        $board = $this->context->fetchNode($type, $id);
        if (!$board || !$board instanceof BoardRecord) {
            throw KadathException::badRequest('board not found');
        }

        $post = $this->populatePostRecord();
        $post->type = PostRecord::POST_TYPE_THREAD;
        $post->parent_id = $board->id;

        $post->validate();
        $this->postRepo->insert($post);

        return $post;
    }

    /**
     * @return PostRecord
     */
    protected function populatePostRecord(): PostRecord
    {
        $post = new PostRecord();
        $post->user_id = $this->context->session()->getCurrentUser()->id;
        $post->flag = 0;
        $post->child_count = 0;
        $post->latest_childs = '[]';
        $post->title = trim($this->args['title'] ?? '');
        $post->content = trim($this->args['content'] ?? '');
        $post->content_type = PostRecord::CONTENT_TYPE_PLAIN;
        $post->created_at = time();
        $post->deleted_at = 0;
        $post->touched_at = Utility::microsecond();
        $post->via = '';

        return $post;
    }
}
