<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\debug\dumper;

use df;
use df\lang;

use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Cloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Caster;

class Handler
{
    /**
     * Register handler
     */
    public static function register(): void
    {
        VarDumper::setHandler([self::class, 'dump']);
    }


    /**
     * Dump an individual var
     */
    public static function dump($var)
    {
        if ('cli' === PHP_SAPI) {
            $dumper = new CliDumper();
        } else {
            $dumper = new HtmlDumper();

            $dumper->setDisplayOptions([
                'maxDepth' => 3
            ]);
        }


        $cloner = new Cloner\VarCloner();

        $cloner->addCasters([
            /*
            'Exception' => function ($object, $array, Cloner\Stub $stub, $isNested, $filter) {
                return $array;
            }
            */
        ]);

        $dumper->dump($cloner->cloneVar($var));
    }
}
