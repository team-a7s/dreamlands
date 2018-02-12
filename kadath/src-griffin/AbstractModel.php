<?php

declare(strict_types=1);

namespace Lit\Griffin;

use Doctrine\Common\Inflector\Inflector;
use GraphQL\Type\Definition\ResolveInfo;
use Kadath\Database\TableBoard;
use Lit\Air\Injection\SetterInjector;
use Lit\Nexus\Interfaces\KeyValueInterface;
use Lit\Nexus\Traits\EmbedKeyValueTrait;
use Lit\Nexus\Traits\KeyValueObjectAccessTrait;

abstract class AbstractModel implements KeyValueInterface, \JsonSerializable, TableBoard
{
    use KeyValueObjectAccessTrait;
    use EmbedKeyValueTrait;

    public const SETTER_INJECTOR = SetterInjector::class;

    /**
     * @var Context
     */
    protected $context;
    /**
     * @var string[]
     */
    protected static $resolvedFields = [];
    protected static $resolverMap = [];
    /**
     * @var AbstractType
     */
    protected $object;
    /**
     * @var ObjectRepositoryInterface
     */
    protected $objectRepository;

    public function __construct(AbstractType $object)
    {
        $this->object = $object;
        $this->innerKeyValue = $this->object;
        $this->objectRepository = $object->getObjectRepository();
        foreach (static::$resolvedFields as $fld) {
            $this->markResolvedField($fld);
        }
        foreach (static::$resolverMap as $fld => $className) {
            /** @var AbstractResolver $className */
            $this->object->set($fld, $className::getResolver());
        }
    }

    public function injectContext(Context $context)
    {
        $this->context = $context;
    }

    public function jsonSerialize()
    {
        return $this->object->getContent();
    }

    public function makeResolver(string $fieldName): \Closure
    {
        return function ($source, array $args, $context, ResolveInfo $resolveInfo) use ($fieldName) {
            $method = lcfirst(Inflector::classify('resolve_' . $fieldName));
            return $this->$method($source, $args, $context, $resolveInfo);
        };
    }

    public function markResolvedField(string $fieldName): self
    {
        $this->object->set($fieldName, $this->makeResolver($fieldName));

        return $this;
    }
}
