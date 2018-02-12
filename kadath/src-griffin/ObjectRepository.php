<?php

declare(strict_types=1);

namespace Lit\Griffin;

use Doctrine\Common\Inflector\Inflector;
use Lit\Air\Factory;
use Lit\Bolt\BoltContainer;

class ObjectRepository implements ObjectRepositoryInterface
{
    public const TYPE_NAMESPACE = '';
    public const MODEL_NAMESPACE = '';
    /**
     * @var Factory
     */
    protected $factory;

    public function __construct(BoltContainer $container)
    {
        $this->factory = Factory::of($container);
    }

    public function create(string $type, array $data = [])
    {
        $className = static::TYPE_NAMESPACE . '\\' . Inflector::classify("{$type}_type");
        $object = new $className($this, $data);

        $modelClassName = static::MODEL_NAMESPACE . '\\' . Inflector::classify("{$type}_model");
        if (class_exists($modelClassName)) {
            return $this->factory->instantiate($modelClassName, [$object]);
        }

        return $object;
    }

    public function export(ExportableInterface $target)
    {
        return $this->create($target->getObjectType(), $target->exportArray());
    }
}
