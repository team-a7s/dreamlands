<?php

declare(strict_types=1);

namespace Kadath\Utility;

use Moontoast\Math\BigNumber;

class Utility
{
    public static function microsecond()
    {
        [$msec, $sec] = explode(' ', microtime());
        $num = new BigNumber($sec, 6);
        $num->add($msec)->multiply(1e6);
        $num->round();

        return $num;
    }

    /**
     * get_object_vars wrapper so only public fields are returned
     * @param $obj
     * @return array
     */
    public static function getObjectVars($obj)
    {
        return get_object_vars($obj);
    }
}
