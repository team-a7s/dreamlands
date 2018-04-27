<?php

declare(strict_types=1);

namespace Kadath\Configuration;

use Kadath\Middlewares\KarmaMiddleware;

/**
 * const container for KarmaMiddleware
 * override this with your parameter
 */
interface KarmaPolicy
{
    const KARMA_TTL = 360;
    const KARMA_CAPABILITY = [
        KarmaMiddleware::KARMA_TYPE_ANONYMOUS => 50,
        KarmaMiddleware::KARMA_TYPE_TURING_SESSION => 50000,
        KarmaMiddleware::KARMA_TYPE_USER_SESSION => 10000,
    ];
}
