<?php

declare(strict_types=1);

namespace Lit\Griffin;

use GraphQL\Type\Definition\ScalarType;
use Lit\Nexus\Derived\OffsetKeyValue;
use Lit\Nexus\Traits\KeyValueObjectAccessTrait;

abstract class AbstractType extends OffsetKeyValue implements \JsonSerializable
{
    use KeyValueObjectAccessTrait;

    protected static $memberTypes = [];
    /**
     * @var ObjectRepositoryInterface
     */
    protected $objectRepository;

    /**
     * @param ObjectRepositoryInterface $objectAdapter
     * @param array $data
     */
    public function __construct(ObjectRepositoryInterface $objectAdapter, array $data = [])
    {
        parent::__construct([]);

        $this->objectRepository = $objectAdapter;
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function jsonSerialize()
    {
        return $this->content;
    }

    public function set($key, $value)
    {
        $type = static::$memberTypes[$key] ?? null;
        if (is_string($type) && is_array($value)) {
            $value = $this->objectRepository->create($type, $value);
        } elseif ($type instanceof ScalarType) {
            $value = $type->serialize($value);
        }

        parent::set($key, $value);
    }

    /**
     * @return ObjectRepositoryInterface
     */
    public function getObjectRepository(): ObjectRepositoryInterface
    {
        return $this->objectRepository;
    }
}
