<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Shell;

use Df;

use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

trait TRenderer
{
    use LoggerTrait;

    protected static $styles = [
        'debug' => ['', ['white', 'bold'], false],
        'info' => ['ℹ ', ['cyan'], false],
        'notice' => ['☛ ', ['cyan', 'bold'], false],
        'success' => ['✓ ', ['green', 'bold'], false],
        'warning' => ['⚠ ', ['yellow'], true],
        'error' => ['✗ ', ['red'], true],
        'critical' => ['☠ ', ['white', 'red', 'bold'], true],
        'alert' => ['✖ ', ['red', 'bold'], true],
        'emergency' => ['✘ ', ['white', 'red', 'bold', 'underline'], true],
    ];

    protected static $fgColors = [
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
        'default' => 38,
        'reset' => 39
    ];

    protected static $bgColors = [
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47,
        'default' => 48,
        'reset' => 49
    ];

    protected static $options = [
        'bold' => [1, 22],
        'underline' => [4, 24],
        'blink' => [5, 25],
        'reverse' => [7, 27],
        'private' => [8, 28]
    ];

    /**
     * Render output from a task
     */
    public function render($output, ?string $modifier=null): void
    {
        if (is_callable($output) && is_object($output)) {
            $this->render($output(), $modifier);
            return;
        }

        if ($output instanceof \Generator) {
            foreach ($output as $modifier => $value) {
                if (is_int($modifier)) {
                    $modifier = null;
                }

                $this->render($value, $modifier);
            }

            return;
        }

        $newLine = true;
        $isError = false;

        if ($modifier !== null) {
            preg_match('/^([^a-zA-Z0-9]*)([a-zA-Z0-9\|]*)$/', $modifier, $matches);
            $mods = $matches[1] ?? null;
            $style = $matches[2] ?? null;

            if (false !== strpos($mods, '+')) {
                $newLine = false;
            }

            if (false !== strpos($mods, '^')) {
                $newLine = 2;
            }

            if (preg_match('/([>]+)/', $mods, $matches)) {
                $count = strlen($matches[1]);
                $output = str_repeat("\t", $count).$output;
            }

            if ($style !== null && $this->canColor()) {
                if (isset(static::$styles[$style])) {
                    [$prefix, $style, $isError] = static::$styles[$style];

                    if ($newLine) {
                        $output = $prefix.$output;
                    }
                } else {
                    $style = explode('|', $style);
                }

                $style = $this->extractStyle($style);
                $output = $this->style($output, $style[0], $style[1], ...$style[2]);
            }

            if (false !== strpos($mods, '!')) {
                $isError = true;
            }

            if (false !== strpos($mods, '!!')) {
                $isError = false;
            }
        }


        if ($isError) {
            if ($newLine) {
                $this->writeErrorLine($output);

                if ($newLine === 2) {
                    $this->writeErrorLine();
                }
            } else {
                $this->writeError($output);
            }
        } else {
            if ($newLine) {
                $this->writeLine($output);

                if ($newLine === 2) {
                    $this->writeLine();
                }
            } else {
                $this->write($output);
            }
        }
    }

    /**
     * Add style info to message
     */
    public function style(string $message, string $fgColor, string $bgColor=null, string ...$options): string
    {
        if ($bgColor === null) {
            $bgColor = 'default';
        }

        $fgCode = static::$fgColors[$fgColor];
        $fgReset = static::$fgColors['reset'];
        $bgCode = static::$bgColors[$bgColor];
        $bgReset = static::$bgColors['reset'];

        $setCodes = [$fgCode, $bgCode];
        $unsetCodes = [$fgReset, $bgReset];

        foreach ($options as $option) {
            $setCodes[] = static::$options[$option][0];
            $unsetCodes[] = static::$options[$option][1];
        }

        return sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $message, implode(';', $unsetCodes));
    }


    /**
     * Render log
     */
    public function success($message, array $context=[])
    {
        $this->log('success', $message, $context);
    }

    public function log($level, $message, array $context=[])
    {
        if (!isset(static::$styles[$level])) {
            $level = 'info';
        }

        $message = $this->interpolate((string)$message, $context);
        [$prefix, $style, $isError] = static::$styles[$level];

        if ($this->canColor()) {
            $style = $this->extractStyle($style);

            $message = $this->style(
                $prefix.$message,
                $style[0],
                $style[1],
                ...$style[2]
            );
        }

        if ($isError) {
            $this->writeErrorLine($message);
        } else {
            $this->writeLine($message);
        }
    }

    private function interpolate(string $message, array $context=[]): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{'.$key.'}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    private function extractStyle(array $styles): array
    {
        $fg = $bg = false;
        $output = ['default', 'default', []];

        foreach ($styles as $style) {
            if (isset(static::$fgColors[$style])) {
                if (!$fg) {
                    $output[0] = $style;
                    $fg = true;
                } elseif (!$bg) {
                    $output[1] = $style;
                    $bg = true;
                }
            } elseif (isset(static::$options[$style])) {
                $output[2][] = $style;
            }
        }

        return $output;
    }
}
