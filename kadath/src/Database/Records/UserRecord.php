<?php

declare(strict_types=1);

namespace Kadath\Database\Records;

use Kadath\Database\AbstractRecord;
use Kadath\Database\TableUser;
use Kadath\Exceptions\KadathException;
use Kadath\GraphQL\NodeIdentify;
use Lit\Griffin\ExportableInterface;
use Lit\Griffin\ExportableTrait;

/**
 * Class UserRecord
 * @package Kadath\Records
 */
class UserRecord extends AbstractRecord implements TableUser, ExportableInterface
{
    use ExportableTrait;
    const OBJECT_TYPE = 'User';
    const NODE_TYPE = NodeIdentify::TYPE_USER;

    public static function create($nickname)
    {
        if (!self::isValidNickname($nickname)) {
            throw new KadathException('非法的昵称');
        }

        $user = new static();
        $user->nickname = $nickname;
        $user->uniq = crc32(uniqid('', true)) % (2 ** 14);
        $user->hash = sha1(uniqid('', true));
        $user->created_at = time();
        $user->expiring_at = time() + 72 * 86400;
        $user->status = 0;

        return $user;
    }

    public static function isValidNickname($nickname)
    {
        return preg_match('/^[a-zA-Z0-9_\-.\x{0800}-\x{9fff}]{1,10}$/u', $nickname);
    }

    public function getDisplayName()
    {
        return sprintf('%s#%d', $this->nickname, $this->uniq);
    }

    public function exportArray(): array
    {
        return [
            'id' => $this->idResolver(),
            'displayName' => $this->getDisplayName(),
//            'avatar' => $this->av,
        ];
    }
}
