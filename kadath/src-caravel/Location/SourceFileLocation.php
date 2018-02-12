<?php

declare(strict_types=1);

namespace Lit\Caravel\Location;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Types\Type;
use Lit\Nexus\Utilities\SimpleTemplate;

class SourceFileLocation extends AbstractLocation
{
    public function read(): Schema
    {
        throw new \Exception('not implemented');
    }

    public function update(SchemaDiff $diff)
    {
        throw new \Exception('not implemented');
    }

    public function write(Schema $schema)
    {
        $context = [
            'namespace' => $this->getConfig('namespace'),
        ];
        $tpl = SimpleTemplate::instance(file_get_contents($this->getConfig('template')));

        foreach ($schema->getTables() as $table) {
            $name = Inflector::classify(sprintf($this->getConfig('name'), $table->getName()));
            $fields = array_map(function (Column $column) {
                $typeMap = [
                    Type::TARRAY => 'array',
                    Type::SIMPLE_ARRAY => 'array',
                    Type::JSON_ARRAY => 'array',
                    Type::JSON => 'array',
                    Type::OBJECT => 'object',
                    Type::BOOLEAN => 'bool',
                    Type::INTEGER => 'int',
                    Type::SMALLINT => 'int',
                    Type::BIGINT => 'int',
                    Type::STRING => 'string',
                    Type::TEXT => 'string',
                    Type::DATETIME => '\DateTimeInterface',
                    Type::DATETIME_IMMUTABLE => '\DateTimeInterface',
                    Type::DATETIMETZ => '\DateTimeInterface',
                    Type::DATETIMETZ_IMMUTABLE => '\DateTimeInterface',
                    Type::DATE => '\DateTimeInterface',
                    Type::DATE_IMMUTABLE => '\DateTimeInterface',
                    Type::TIME => '\DateTimeInterface',
                    Type::TIME_IMMUTABLE => '\DateTimeInterface',
                    Type::DECIMAL => 'string',
                    Type::FLOAT => 'float',
                    Type::BINARY => 'string',
                    Type::BLOB => 'string',
                    Type::GUID => 'string',
                    Type::DATEINTERVAL => '\DateInterval',
                ];

                return [
                    'phpType' => $typeMap[$column->getType()->getName()] ?? 'mixed',
                    'name' => $column->getName(),
                ];
            }, $table->getColumns());

            $source = $tpl->render([
                    'name' => $name,
                    'fields' => $fields,
                ] + $context);

            $filename = $this->getConfig('directory') . DIRECTORY_SEPARATOR . $name . '.php';

            file_put_contents($filename, $source);
        }
    }
}
