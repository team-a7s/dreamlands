<?php

/**
 * AUTO GENERATED, DO NOT EDIT
 */

declare(strict_types=1);

`if !empty($namespace)
namespace `$namespace`;
`/if

/**
`loop $fields $i $fld
 * @property `$fld['phpType']` $`$fld['name']`
`/loop
*/
interface `$name`
{
}
