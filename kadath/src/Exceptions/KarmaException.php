<?php

declare(strict_types=1);

namespace Kadath\Exceptions;

use GraphQL\Error\ClientAware;

class KarmaException extends \Exception implements ClientAware
{
    public static function confess()
    {
        return new self('too many karma');
    }

    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return 'karma';
    }

}
