<?php

declare(strict_types=1);

namespace Kadath\Database\Records;

use GraphQL\Type\Definition\ResolveInfo;
use Kadath\Database\AbstractRecord;
use Kadath\Database\TablePost;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\KadathContext;
use Kadath\GraphQL\NodeIdentify;
use Kadath\GraphQL\Resolvers\Board\PostsQuery;
use Kadath\GraphQL\Type\ContentTypeEnum;
use Kadath\GraphQL\Type\PostTypeEnum;
use Lit\Griffin\ExportableInterface;
use Lit\Griffin\ExportableTrait;

class PostRecord extends AbstractRecord implements TablePost, ExportableInterface
{
    use ExportableTrait;

    const OBJECT_TYPE = 'Post';
    const NODE_TYPE = NodeIdentify::TYPE_POST;

    const POST_TYPE_THREAD = 1;
    const POST_TYPE_POST = 2;
    const POST_TYPE_PROFILE = 3;
    const CONTENT_TYPE_PLAIN = 1;

    public static $postType = [
        PostTypeEnum::THREAD => self::POST_TYPE_THREAD,
        PostTypeEnum::POST => self::POST_TYPE_POST,
        PostTypeEnum::PROFILE => self::POST_TYPE_PROFILE,
    ];

    public static $contentType = [
        ContentTypeEnum::PLAIN => self::CONTENT_TYPE_PLAIN,
    ];
    public static $parentNodeType = [
        self::POST_TYPE_THREAD => NodeIdentify::TYPE_BOARD,
        self::POST_TYPE_POST => NodeIdentify::TYPE_POST,
        self::POST_TYPE_PROFILE => NodeIdentify::TYPE_USER,
    ];

    public function exportArray(): array
    {
        return [
            'id' => $this->idResolver(),
            'author' => function ($src, array $args, KadathContext $context, ResolveInfo $resolveInfo) {
                return $context->fetchNode(NodeIdentify::TYPE_USER, $this->user_id);
            },
            'title' => $this->title,
            'type' => array_search($this->type, self::$postType),
            'content' => $this->content,
            'contentType' => $this->content_type,
            'via' => $this->via,
            'childCount' => $this->child_count,
            'created' => $this->created_at,
            'parentId' => function ($src, array $args, KadathContext $context, ResolveInfo $resolveInfo) {
                return $context->nodeIdentify->encodeId(self::$parentNodeType[$this->type], $this->parent_id);
            },
            'parentNode' => function ($src, array $args, KadathContext $context, ResolveInfo $resolveInfo) {
                return $context->fetchNode(self::$parentNodeType[$this->type], $this->parent_id);
            },
            'posts' => $this->delegateResolver(PostsQuery::class),
        ];
    }

    public function validate()
    {
        if (empty($this->title) && $this->type === self::POST_TYPE_THREAD) {
            throw KadathException::badRequest('empty title');
        }
        if (empty($this->content) && $this->type === self::POST_TYPE_POST) {
            throw KadathException::badRequest('empty content');
        }

        if (mb_strlen($this->title) > 30) {
            throw KadathException::badRequest('title too long');
        }
        if (mb_strlen($this->content) > 401) {
            throw KadathException::badRequest('content too long');
        }
    }
}
