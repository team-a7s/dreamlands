<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class GlobalFieldResolver
{
    /**
     * @var ObjectRepositoryInterface
     */
    protected $objectRepository;

    public function __construct(ObjectRepositoryInterface $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    public function resolve($source, array $args, Context $context, ResolveInfo $resolveInfo)
    {
        if ($source === null) {
            $source = $this->objectRepository->create($resolveInfo->parentType->name);
        }
        $field = $resolveInfo->fieldName;
        $value = null;

        if ($source instanceof ExportableInterface) {
            $source = $this->objectRepository->export($source);
        }

        if ((is_array($source) || $source instanceof \ArrayAccess) && isset($source[$field])) {
            $value = $source[$field];
        } elseif (is_object($source) && isset($source->{$field})) {
            $value = $source->{$field};
        }

        if ($value === null && $resolveInfo->returnType instanceof NonNull) {
            $returnType = $resolveInfo->returnType->getWrappedType();
            if ($returnType instanceof ObjectType) {
                $value = $this->objectRepository->create($returnType->name, $args);
            }
        }

        return $value instanceof \Closure ? $value($source, $args, $context, $resolveInfo) : $value;
    }

    public function __invoke(...$args)
    {
        return $this->resolve(...$args);
    }
}
