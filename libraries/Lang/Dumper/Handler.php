<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\lang\dumper;

use df;
use df\lang\IDumper;

class Handler
{
    public static function createGeneric()
    {
        return new Symfony();
    }

    public static function getDumper(): IDumper
    {
        if (!defined('df\\BOOTSTRAPPED')) {
            return self::createGeneric();
        } else {
            $app = df\app();

            if ($app->has('lang.dumper')) {
                return $app['lang.dumper'];
            } else {
                return self::createGeneric();
            }
        }
    }

    public static function dump(...$vars): void
    {
        static::getDumper()->dump(...$vars);
    }

    public static function dumpDie(...$vars): void
    {
        static::getDumper()->dumpDie(...$vars);
    }
}
