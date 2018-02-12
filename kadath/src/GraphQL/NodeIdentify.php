<?php

declare(strict_types=1);

namespace Kadath\GraphQL;

use Hashids\Hashids;
use Kadath\Database\AbstractRecord;
use Kadath\Database\AbstractRepository;
use Kadath\Database\Repositories\BoardRepo;
use Kadath\Database\Repositories\MemberRepo;
use Kadath\Database\Repositories\PostRepo;
use Kadath\Database\Repositories\UserRepo;
use Kadath\Exceptions\KadathException;
use Ramsey\Uuid\Uuid;

class NodeIdentify
{
    const TYPE_USER = 1;
    const TYPE_MEMBER = 2;
    const TYPE_BOARD = 11;
    const TYPE_POST = 12;

    public static $recordClass;
    public static $typePkField;
    public static $meta = [
        self::TYPE_USER => [
            UserRepo::class,
        ],
        self::TYPE_MEMBER => [
            MemberRepo::class,
        ],
        self::TYPE_BOARD => [
            BoardRepo::class,
        ],
        self::TYPE_POST => [
            PostRepo::class,
        ],
    ];
    /**
     * @var Hashids
     */
    protected $hashids;

    public function __construct(Hashids $hashids)
    {
        $this->hashids = $hashids;
    }

    public function getId(AbstractRecord $record): ?string
    {
        $type = static::getTypeOfRecord($record);
        if (!$type) {
            return null;
        }

        $ids = [$type];

        $pkVal = $record->{static::$typePkField[$type]};
        if (!is_int($pkVal) && strval(intval($pkVal)) !== $pkVal) {
            if (Uuid::isValid($pkVal)) {
                $uuid = Uuid::fromString($pkVal);
                $ids[] = strval($uuid->getInteger());
            } else {
                throw new \Exception(__METHOD__ . '/' . __LINE__);
            }
        } else {
            $ids[] = $pkVal;
        }

        return $this->hashids->encode(...$ids);
    }

    public function encodeId(int $type, ...$ids): string
    {
        return $this->hashids->encode($type, ...$ids);
    }

    public function decodeId(string $id): array
    {
        $decode = $this->hashids->decode($id);
        if (count($decode) < 2) {
            throw KadathException::badRequest('bad_id');
        }
        [$type, $id] = $decode;
        if (!isset(self::$meta[$type])) {
            throw KadathException::badRequest('bad_id');
        }

        return [$type, $id];
    }

    public function getTypeOfRecord(AbstractRecord $record): ?int
    {
        if (!isset(static::$recordClass)) {
            foreach (static::$meta as $type => [$repoClass]) {
                /** @var AbstractRepository $repoClass */
                static::$recordClass[$repoClass::RECORD_CLASS] = $type;
                static::$typePkField[$type] = $repoClass::PK_FIELD;
            }
        }

        return static::$recordClass[get_class($record)] ?? null;
    }

    /**
     * @return Hashids
     */
    public function getHashids(): Hashids
    {
        return $this->hashids;
    }
}
