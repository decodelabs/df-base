<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df;

use df;
use df\lang;

/**
 * This is just a facade.
 * See lang\error
 */
class Error
{
    const TYPE = null;

    public static function __callStatic(string $method, array $args): lang\error\IError
    {
        return lang\error\Factory::create(
            static::TYPE,
            $args[0] ?? null,
            $args[1] ?? [],
            explode(',', $method)
        );
    }

    private function __construct() {}
}
