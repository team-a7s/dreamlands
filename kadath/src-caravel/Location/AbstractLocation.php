<?php

declare(strict_types=1);

namespace Lit\Caravel\Location;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;

abstract class AbstractLocation
{
    public static $map = [
        'db' => DatabaseLocation::class,
        'schema' => SchemaLocation::class,
        'sql' => SqlLocation::class,
        'class' => SourceFileLocation::class,
    ];

    /**
     * @var array
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function getConfig($key, $default = null)
    {
        if (!isset($this->config[$key])) {
            return $default;
        }

        return preg_replace_callback('/\{(\w+)\}/', function ($match) {
            return $_ENV[$match[1]] ?? $match[0];
        }, $this->config[$key]);
    }

    public static function create(array $location): ?self
    {
        if (isset(self::$map[$location['type'] ?? ''])) {
            $class = self::$map[$location['type']];
            return new $class($location);
        }

        return null;
    }

    abstract public function read(): Schema;

    abstract public function update(SchemaDiff $diff);

    public function diff(Schema $schema): SchemaDiff
    {
        $cmp = new Comparator();
        return $cmp->compare($this->read(), $schema);
    }

    public function write(Schema $schema)
    {
        $diff = $this->diff($schema);
        $this->update($diff);
    }
}
