<?php

declare(strict_types=1);

namespace Kadath\Utility;

class IdGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return uniqid(dechex(crc32(gethostname()) % 0x10000), true) . dechex(getmypid());
    }
}
