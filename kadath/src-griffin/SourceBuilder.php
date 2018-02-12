<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Language\Parser;
use GraphQL\Utils\AST;
use Lit\Nexus\Interfaces\SingleValueInterface;

class SourceBuilder
{
    /**
     * @var SingleValueInterface
     */
    protected $cache;
    /**
     * @var string
     */
    protected $path;

    public function __construct(SingleValueInterface $cache, string $path)
    {

        $this->cache = $cache;
        $this->path = $path;
    }

    public function build()
    {
        if ($this->cache->exists()) {
            return AST::fromArray(unserialize($this->cache->get(), ["allowed_classes" => false]));
        }
        $document = Parser::parse(file_get_contents($this->path));
        $this->cache->set(serialize(AST::toArray($document)));

        return $document;
    }
}
