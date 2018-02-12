<?php

declare(strict_types=1);

namespace Lit\Caravel\Location;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class SchemaLocation extends AbstractLocation
{
    public function read(): Schema
    {
        $schema = new Schema();
        foreach ($this->finder() as $file) {
            $this->createTable($schema, $file);
        }
        return $schema;
    }

    public function update(SchemaDiff $diff)
    {
        throw new \Exception('not implemented');
    }

    public function write(Schema $schema)
    {
        foreach ($schema->getTables() as $table) {
            $this->writeTable($table);
        }
    }


    protected function createTable(Schema $schema, SplFileInfo $fileInfo)
    {
        $yml = Yaml::parse($fileInfo->getContents());
        $table = $schema->createTable($yml['name']);

        foreach ($yml['columns'] as $column) {
            $option = $column['option'] ?? [];

            if (isset($column['comment'])) {
                $option['comment'] = $column['comment'];
            }

            $table->addColumn($column['name'], $column['type'], $option);
        }

        if (isset($yml['indexes'])) {
            foreach ($yml['indexes'] as $idx) {
                $name = $idx['name'] ?? self::getIndexName($yml['name'], $idx['columns']);
                $flags = $idx['flags'] ?? [];
                $option = $idx['option'] ?? [];

                if (isset($idx['comment'])) {
                    $option['comment'] = $idx['comment'];
                }

                if (!empty($idx['unique'])) {
                    $table->addUniqueIndex($idx['columns'], $name, $option);
                } else {
                    $table->addIndex($idx['columns'], $name, $flags, $option);
                }
            }
        }
        if (isset($yml['pk'])) {
            $table->setPrimaryKey($yml['pk']);
        }

        if (isset($yml['charset'])) {
            $table->addOption('charset', $yml['charset']);
        }
        if (isset($yml['collate'])) {
            $table->addOption('collate', $yml['collate']);
        }

        if (!empty($yml['fk'])) {
            foreach ($yml['fk'] as $fk) {
                $table->addForeignKeyConstraint(
                    $fk['table'],
                    $fk['key'],
                    $fk['foreign_key'],
                    $fk['options'] ?? [],
                    $fk['name'] ?? null
                );
            }
        }
    }

    protected function finder()
    {
        $dir = $this->getConfig('directory');
        $pattern = '*.yml';

        return Finder::create()
            ->files()->in($dir)
            ->name($pattern);
    }

    protected function writeTable(Table $table): void
    {
        $indexes = $table->getIndexes();
        $namespaceName = $table->getNamespaceName();
        $arr = [
            'name' => $table->getName(),
            'columns' => array_values(array_map([$this, 'dumpColumn'], $table->getColumns())),
            'indexes' => array_values(array_filter(array_map([$this, 'dumpIndex'],
                $indexes,
                array_fill(0, count($indexes), $table->getName())
            ))),
            'pk' => $table->getPrimaryKeyColumns(),
            'comment' => $table->getOptions()['comment'] ?? '',
        ];

        if (!empty($namespaceName)) {
            $arr += ['namespace' => $namespaceName];
        }

        $filename = $this->getConfig('directory') . DIRECTORY_SEPARATOR . $arr['name'] . '.yml';

        file_put_contents($filename, Yaml::dump($arr, 4, 2));
    }

    protected function dumpColumn(Column $column)
    {
        $arr = [
            'name' => $column->getName(),
            'type' => $column->getType()->getName(),
        ];

        $comment = $column->getComment();
        if (!empty($comment)) {
            $arr += ['comment' => $comment];
        }

        $option = [];
        if (!$column->getNotnull()) {
            $option['notnull'] = false;
        }

        foreach ([
                     'length' => null,
                     'precision' => 10,
                     'scale' => 0,
                     'unsigned' => false,
                     'fixed' => false,
                     'default' => null,
                     'platformOptions' => [],
                     'autoincrement' => false,
                     'customSchemaOptions' => [],
                 ] as $k => $default) {

            $val = ([$column, "get$k"])();
            if ($val === $default) {
                continue;
            }

            $option[$k] = $val;
        }

        if (!empty($option)) {
            $arr += [
                'option' => $option,
            ];
        }
        return $arr;
    }

    protected function dumpIndex(Index $index, string $tableName = '')
    {
        if ($index->isPrimary()) {
            return null;
        }
        $arr = [
            'columns' => $index->getColumns(),
        ];

        $name = $index->getName();
        if ($name !== self::getIndexName($tableName, $index->getColumns())) {
            $arr += [
                'name' => $name
            ];
        }

        $options = $index->getOptions();
        if (isset($options['comment'])) {
            $comment = $options['comment'];
            unset($options['comment']);
        } else {
            $comment = '';
        }
        if (!empty($options)) {
            $arr += [
                'option' => $options,
            ];
        }
        if (!empty($comment)) {
            $arr += [
                'comment' => $comment,
            ];
        }

        $flags = $index->getFlags();
        if (!empty($flags)) {
            $arr += [
                'flags' => $flags,
            ];
        }

        if ($index->isUnique()) {
            $arr['unique'] = true;
        }

        return $arr;
    }

    protected static function getIndexName(string $tableName, array $columnNames)
    {
        $arr = array_merge([$tableName], $columnNames);

        return Inflector::tableize('idx__' . implode('__', $arr));
    }
}
