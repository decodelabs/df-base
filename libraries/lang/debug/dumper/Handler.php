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
        VarDumper::setHandler([self::class, 'dumpOne']);
    }


    /**
     * Main dump handler
     */
    public static function dump(...$vars): void
    {
        http_response_code(500);

        $call = lang\debug\StackCall::create(1);

        $attrs = [
            'time' => self::formatMicrotime(microtime(true) - df\START),
            'memory' => self::formatFilesize(memory_get_usage()),
            'location' => $call->getCallingFile().' : '.$call->getCallingLine()
        ];

        if ('cli' === PHP_SAPI) {
            echo implode(' | ', $attrs)."\n\n";
        } else {
            echo '<pre class="sf-dump">'.implode(' | ', $attrs).'</pre>';
        }

        foreach ($vars as $var) {
            self::dumpOne($var);
        }

        die(1);
    }


    /**
     * Dump an individual var
     */
    public static function dumpOne($var)
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

        /*
        $cloner->addCasters([
            'test' => function ($object, $array, Cloner\Stub $stub, $isNested, $filter) {
                return $array;
            }
        ]);
        */

        $dumper->dump($cloner->cloneVar($var));
    }





    /**
     * TODO: move these to a shared location
     */
    private static function formatFilesize($bytes)
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    private static function formatMicrotime($time)
    {
        return number_format($time * 1000, 2).' ms';
    }
}
