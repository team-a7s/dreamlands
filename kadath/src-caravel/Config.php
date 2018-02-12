<?php

declare(strict_types=1);

namespace Lit\Caravel;

use Lit\Nexus\Derived\OffsetKeyValue;
use Lit\Nexus\Traits\KeyValueArrayAccessTrait;
use src\Lit\Nexus\Utilities\Json;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Symfony\Component\Yaml\Yaml;

class Config extends OffsetKeyValue implements \ArrayAccess
{
    use KeyValueArrayAccessTrait;

    protected static $extensionMap = [
        'yml' => 'yml',
        'yaml' => 'yml',
        'json' => 'json',
    ];

    public function __construct()
    {
        parent::__construct(null);
    }

    public function load(string $path)
    {
        if (is_file($path)) {
            $this->content = $this->tryLoad($path);
            if (is_null($this->content)) {
                throw new RuntimeException('failed to load ' . $path);
            }

            return;
        }

        if (is_dir($path)) {
            foreach (array_keys(self::$extensionMap) as $ext) {
                $filename = $path . DIRECTORY_SEPARATOR . 'caravel.' . $ext;
                if (is_file($filename)) {
                    $this->content = $this->tryLoad($filename);
                    if (!is_null($this->content)) {
                        return;
                    }
                }
            }
        }

        throw new RuntimeException('failed to find config ' . $path);
    }

    protected function tryLoad(string $filename): ?array
    {
        assert(is_readable($filename));

        $ext = substr($filename, strrpos($filename, '.') + 1);
        if (!isset(self::$extensionMap[$ext])) {
            throw new RuntimeException('unknown extension for config file ' . $filename);
        }

        if (self::$extensionMap[$ext] === 'yml') {
            return Yaml::parseFile($filename);
        } else {
            if (self::$extensionMap[$ext] === 'json') {
                $content = file_get_contents($filename);
                return Json::decode($content, true);
            }
        }

        return null;
    }
}
