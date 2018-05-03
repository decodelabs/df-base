<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df;

use df;
use df\lang\error\Factory;

/**
 * This is just a facade.
 * See lang\error
 */
class Error
{
    const TYPE = null;

    public static function __callStatic(string $method, array $args): IError
    {
        return Factory::create(
            static::TYPE,
            explode(',', $method),
            ...$args
        );
    }

    private function __construct()
    {
    }
}
