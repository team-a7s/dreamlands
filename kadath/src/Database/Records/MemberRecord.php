<?php

declare(strict_types=1);

namespace Kadath\Database\Records;

use Kadath\Database\AbstractRecord;
use Kadath\Database\TableMember;
use Kadath\GraphQL\NodeIdentify;
use Lit\Griffin\ExportableInterface;
use Lit\Griffin\ExportableTrait;

class MemberRecord extends AbstractRecord implements TableMember, ExportableInterface
{
    use ExportableTrait;
    const OBJECT_TYPE = 'User';
    const NODE_TYPE = NodeIdentify::TYPE_MEMBER;

    public function exportArray(): array
    {
        return [
            'id' => $this->idResolver(),
            'openid' => $this->openid,
        ];
    }
}
