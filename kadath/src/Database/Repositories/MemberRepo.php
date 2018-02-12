<?php

declare(strict_types=1);

namespace Kadath\Database\Repositories;

use Kadath\Database\AbstractRepository;
use Kadath\Database\Records\MemberRecord;
use Kadath\Exceptions\KadathException;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class MemberRepo
 * @package Kadath\Repositories
 * @method MemberRecord find(string $id)
 */
class MemberRepo extends AbstractRepository
{
    const RECORD_CLASS = MemberRecord::class;

    public function findOrCreate(ResourceOwnerInterface $resourceOwner): MemberRecord
    {
        $openId = self::getOpenId($resourceOwner);
        $member = $this->find($openId);
        if ($member) {
            return $member;
        }

        $member = new MemberRecord();
        $member->openid = $openId;
        $member->vendor = explode(':', $openId, 2)[0];
        $member->vendor_info = $resourceOwner->toArray();
        $member->created_at = time();

        return $member;
    }

    public static function getOpenId(ResourceOwnerInterface $resourceOwner): string
    {
        switch (true) {
            case $resourceOwner instanceof GithubResourceOwner:
                return 'github:' . $resourceOwner->getId();
            default:
                throw KadathException::auth('bad oauth user');
        }
    }

}
