<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;

class TypeConfigDecorator
{
    /**
     * @see https://webonyx.github.io/graphql-php/type-system/type-language/#defining-resolvers
     */
    public function __invoke($typeConfig, TypeDefinitionNode $typeDefinitionNode)
    {
        if ($typeDefinitionNode instanceof InterfaceTypeDefinitionNode) {
            $typeConfig['resolveType'] = function ($value, Context $context, ResolveInfo $resolveInfo) {
                if ($value instanceof ExportableInterface) {
                    return $value->getObjectType();
                } else {
                    throw new \Exception(__METHOD__ . '/' . __LINE__);
                }
            };
        }

        return $typeConfig;
    }


}
