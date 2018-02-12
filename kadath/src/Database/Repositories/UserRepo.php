<?php

declare(strict_types=1);

namespace Kadath\Database\Repositories;

use Kadath\Database\AbstractRepository;
use Kadath\Database\Records\UserRecord;
use Kadath\Exceptions\KadathException;

/**
 * Class UserRepo
 * @package Kadath\Repositories
 *
 * @method UserRecord find(string $id)
 */
class UserRepo extends AbstractRepository
{
    const RECORD_CLASS = UserRecord::class;

    public function spawn(string $nickname, string $ip): UserRecord
    {
        $count = 0;
        do {
            if (++$count > 3) {
                throw new KadathException('自古枪兵幸运E');
            }

            $user = UserRecord::create($nickname);
        } while ($this->byDisplayname($user->getDisplayName()));

        $user->last_ip = $ip;
        $rowCnt = $this->insert($user);
        assert($rowCnt === 1);

        return $user;
    }

    /**
     * @param string $displayName
     * @return UserRecord
     * @throws \Exception
     */
    public function byDisplayname(string $displayName): ?UserRecord
    {
        if (false === strpos($displayName, '#')) {
            return null;
        }

        [$nickname, $uniq] = explode('#', $displayName);

        return $this->selectFirst([
            'nickname' => $nickname,
            'uniq' => $uniq,
        ]);
    }
}
