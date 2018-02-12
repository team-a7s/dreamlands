<?php

declare(strict_types=1);

namespace Lit\Griffin;

use Doctrine\Common\Inflector\Inflector;
use GraphQL\Language\AST\DefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\EnumTypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\SchemaDefinitionNode;
use GraphQL\Language\AST\UnionTypeDefinitionNode;
use GraphQL\Type\Definition\Type;
use League\Flysystem\FilesystemInterface;
use Lit\Nexus\Utilities\SimpleTemplate;

class ObjectClassGenerator
{
    const CLASS_TPL = <<<'PHP'
<?php

/**
 * AUTO GENERATED, DO NOT EDIT
 */
 
declare(strict_types=1);

`if !empty($namespace)
namespace `$namespace`;
`/if

`if !$isInterface
use Lit\Griffin\AbstractType;
`/if

/**
`loop $fields $i $fld
`if isset($fld['args'])
 * @method `$fld['phpType']` `$fld['name']`(`#
`loop $fld['args'] $j $arg
`=$j>0?',':''` `$arg['phpType']` $`$arg['name']
`/loop
 ) Note: this is NOT a php method but GraphQL field with argument
`else
 * @property `$fld['phpType']` $`$fld['name']`
`/if
`/loop
 */
`if $isInterface
interface `$className`
`else
class `$className` extends AbstractType`if !empty($interfaces)` implements `=implode(', ', $interfaces)``/if`
`/if
{

}
PHP;
    const ENUM_TPL = <<<'PHP'
<?php

/**
 * AUTO GENERATED, DO NOT EDIT
 */
 
declare(strict_types=1);

`if !empty($namespace)
namespace `$namespace`;
`/if

class `$className`
{
`loop $fields $i $fld
    const `$fld` = '`$fld`'; 
`/loop
}
PHP;
    protected $enumTemplate;
    /**
     * @var SimpleTemplate
     */
    protected $template;
    /**
     * @var string
     */
    protected $classNameTemplate;
    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;
    /**
     * @var string
     */
    protected $mutationName = 'Mutation';
    /**
     * @var string
     */
    protected $enumNameTemplate;

    protected $enums = [];
    protected $interfaces = [];
    protected $unions = [];

    public function __construct(
        FilesystemInterface $filesystem,
        string $namespace = '',
        string $classNameTemplate = '%s_type',
        string $enumNameTemplate = '%s_enum'
    ) {
        $this->classNameTemplate = $classNameTemplate;
        $this->namespace = $namespace;
        $this->filesystem = $filesystem;
        $this->template = SimpleTemplate::instance(static::CLASS_TPL);
        $this->enumTemplate = SimpleTemplate::instance(static::ENUM_TPL);
        $this->enumNameTemplate = $enumNameTemplate;
    }


    public function generate(DocumentNode $documentNode)
    {
        foreach ($documentNode->definitions as $definition) {
            $this->previsitDefinition($definition);
        }
        foreach ($documentNode->definitions as $definition) {
            $this->visitDefinition($definition);
        }
    }

    protected function previsitDefinition(DefinitionNode $definition)
    {
        switch (true) {
            case $definition instanceof EnumTypeDefinitionNode:
                $this->previsitEnumTypeDefinitionNode($definition);
                break;
            case $definition instanceof ObjectTypeDefinitionNode:
                $this->previsitObjectTypeDefinitionNode($definition);
                break;
            case $definition instanceof UnionTypeDefinitionNode:
                $this->previsitUnionTypeDefinitionNode($definition);
                break;
            default:
                return;
        }
    }

    protected function visitDefinition(DefinitionNode $definition)
    {
        switch (true) {
            case $definition instanceof SchemaDefinitionNode:
                $this->visitSchemaDefinitionNode($definition);
                break;
            case $definition instanceof ObjectTypeDefinitionNode:
            case $definition instanceof InputObjectTypeDefinitionNode:
            case $definition instanceof InterfaceTypeDefinitionNode:
                $this->visitObjetTypeDefinition($definition);
                break;
            default:
                return;
        }
    }

    /**
     * @param InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $definitionNode
     */
    protected function visitObjetTypeDefinition($definitionNode)
    {
        $name = $definitionNode->name->value;
        $className = $this->makeClassName($name);
        $isMutation = $name == $this->mutationName;
        $isInterface = $definitionNode instanceof InterfaceTypeDefinitionNode;

        $fields = [];
        foreach ($definitionNode->fields as $fieldNode) {

            $result = [];
            /** @var FieldDefinitionNode|InputValueDefinitionNode $fieldNode */
            $result['name'] = $fieldNode->name->value;
            $result['phpType'] = $this->typeToPhpDoc($fieldNode->type);
            if ($fieldNode instanceof FieldDefinitionNode && count($fieldNode->arguments) > 0) {
                $result['args'] = array_map(function (InputValueDefinitionNode $node) {
                    return [
                        'name' => $node->name->value,
                        'phpType' => $this->typeToPhpDoc($node->type),
                    ];
                }, is_array($fieldNode->arguments) ? $fieldNode->arguments : iterator_to_array($fieldNode->arguments));
            } elseif ($isMutation) {
                $result['args'] = [];
            }

            $fields[] = $result;
        }

        $interfaces = [];
        if (!empty($definitionNode->interfaces)) {
            foreach ($definitionNode->interfaces as $interface) {
                $interfaces[] = $this->makeClassName($interface->name->value);
            }
        }

        $data = get_defined_vars() + get_object_vars($this);
        $this->filesystem->put($className . '.php', $this->template->render($data));

        echo 'generating ', $className, PHP_EOL;
    }

    /**
     * @param $typeNode
     * @return array
     */
    protected function explainType($typeNode)
    {
        if ($typeNode instanceof NonNullTypeNode) {
            return ['nonnull' => true] + self::explainType($typeNode->type);
        }
        if ($typeNode instanceof ListTypeNode) {
            return ['itemType' => self::explainType($typeNode->type)];
        }

        if ($typeNode instanceof NamedTypeNode) {
            return [
                'type' => $this->typeName($typeNode),
                'isEnum' => isset($this->enums[$typeNode->name->value])
            ];
        }

        assert(false, new \Exception('bad node'));
        return [];
    }

    /**
     * @param $typeNode
     * @return string
     */
    protected function typeToPhpDoc($typeNode)
    {
        $type = $this->explainType($typeNode);
        $result = '';
        $isList = false;
        if (empty($type['nonnull'])) {
            $result .= 'null|';
        }

        if (isset($type['itemType'])) {
            $itemType = $type['itemType'];
            if (empty($itemType['nonnull']) || isset($itemType['isEnum'])) {
                $result .= 'array';
            } else {
                $result .= $itemType['type'] . '[]';
            }
        } else {
            assert(isset($type['type']));
            $result .= $type['type'];
        }

        return $result;
    }

    protected function typeName(NamedTypeNode $type)
    {
        if ($type instanceof NamedTypeNode) {
            $type = $type->name;
        }
        assert($type instanceof NameNode);
        $name = $type->value;
        if (isset($this->enums[$name])) {
            return 'string';
        }
        if (isset($this->unions[$name])) {
            return implode('|', array_map(function ($name) {
                return isset($this->enums[$name]) ? 'string' : $this->makeClassName($name);
            }, $this->unions[$name]));
        }

        $types = Type::getInternalTypes();
        if (isset($types[$name])) {
            return [
                Type::ID => 'string',
                Type::STRING => 'string',
                Type::FLOAT => 'float',
                Type::INT => 'int',
                Type::BOOLEAN => 'bool',
            ][$name];
        }

        return implode('\\', [
            '',
            $this->namespace,
            $this->makeClassName($name)
        ]);
    }

    /**
     * @param $name
     * @return string
     */
    protected function makeClassName($name): string
    {
        return Inflector::classify(sprintf($this->classNameTemplate, $name));
    }

    /**
     * @param $name
     * @return string
     */
    protected function makeEnumName($name): string
    {
        return Inflector::classify(sprintf($this->enumNameTemplate, $name));
    }

    protected function visitSchemaDefinitionNode(SchemaDefinitionNode $definition)
    {
        foreach ($definition->operationTypes as $operationType) {
            if ($operationType->operation === 'mutation') {
                $this->mutationName = $operationType->type->name->value;
            }
        }
    }

    protected function previsitEnumTypeDefinitionNode(EnumTypeDefinitionNode $definitionNode)
    {
        $name = $definitionNode->name->value;
        $this->enums[$name] = true;
        $className = $this->makeEnumName($name);
        $fields = [];
        foreach ($definitionNode->values as $value) {
            $fields[] = $value->name->value;
        }

        $data = get_defined_vars() + get_object_vars($this);
        $this->filesystem->put($className . '.php', $this->enumTemplate->render($data));

        echo 'generating ', $className, PHP_EOL;
    }

    protected function previsitObjectTypeDefinitionNode(ObjectTypeDefinitionNode $definition)
    {
        $name = $definition->name->value;
        foreach ($definition->interfaces as $interface) {
            $this->interfaces[$interface->name->value][$name] = true;
        }
    }

    private function previsitUnionTypeDefinitionNode(UnionTypeDefinitionNode $definition)
    {
        $name = $definition->name->value;
        $classes = [];
        foreach ($definition->types as $type) {
            $classes[] = $type->name->value;
        }
        $this->unions[$name] = $classes;
    }
}
