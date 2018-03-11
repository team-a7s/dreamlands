<?php

declare(strict_types=1);

namespace Kadath\Adapters;

use Predis\Command\ScriptCommand;

class IncrWithSupremumTtl extends ScriptCommand
{
    const ID = 'INCRWSUPTTL';
    const SCRIPT = <<<'LUA'
local val = redis.call('GET', KEYS[1])
local karma
if val == false then
    karma = 0
else
    karma = tonumber(val)
end

karma = karma + tonumber(KEYS[2])

if karma > tonumber(KEYS[3]) then
    return -1
end

if val == false then
    redis.call('SET', KEYS[1], karma, 'EX', KEYS[4], 'NX')
else
    redis.call('INCRBY', KEYS[1], KEYS[2])
end

return karma
LUA;

    public function getScript()
    {
        return self::SCRIPT;
    }

    protected function getKeysCount()
    {
        return 4;
    }
}
