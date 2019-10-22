<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Command;

class Styles
{
    const FG_COLORS = [
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

    const BG_COLORS = [
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

    const OPTIONS = [
        'bold' => [1, 22],
        'dim' => [2, 22],
        'underline' => [4, 24],
        'blink' => [5, 25],
        'reverse' => [7, 27],
        'private' => [8, 28]
    ];

    protected $color = true;

    protected $styles = [
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

    /**
     * Init with options
     */
    public function __construct(bool $color=true)
    {
        $this->color = $color;
    }


    /**
     * Get style definition
     */
    public function get(string $name): ?array
    {
        return $this->styles[$name] ?? null;
    }

    /**
     * Apply style definition
     */
    public function apply(string $name, string $message, string &$channel=null): string
    {
        $channel = 'out';

        if ((!$style = $this->get($name)) || !$this->color) {
            return $message;
        }

        [$prefix, $style, $isError] = $style;

        if ($this->color) {
            $style = $this->extract($style);

            $message = $this->format(
                $prefix.$message,
                $style[0],
                $style[1],
                ...$style[2]
            );
        }

        return $message;
    }



    /**
     * Add style info to message
     */
    public function format(string $message, ?string $fgColor, ?string $bgColor=null, string ...$options): string
    {
        $setCodes = [];
        $unsetCodes = [];

        if ($fgColor !== null) {
            $setCodes[] = static::FG_COLORS[$fgColor];
            $unsetCodes[] = static::FG_COLORS['reset'];
        }

        if ($bgColor !== null) {
            $setCodes[] = static::BG_COLORS[$bgColor];
            $unsetCodes[] = static::BG_COLORS['reset'];
        }

        foreach ($options as $option) {
            $setCodes[] = static::OPTIONS[$option][0];
            $unsetCodes[] = static::OPTIONS[$option][1];
        }

        return sprintf("\033[%sm%s\033[%sm", implode(';', $setCodes), $message, implode(';', $unsetCodes));
    }

    /**
     * Apply modifiers to string
     */
    public function modify(string $message, ?string $modifier=null, string &$channel=null): string
    {
        $newLines = 1;
        $isError = false;
        $channel = 'out';

        if ($modifier === null) {
            return $message;
        }

        preg_match('/^([^a-zA-Z0-9]*)([a-zA-Z0-9\|]*)$/', $modifier, $matches);
        $mods = $matches[1] ?? null;
        $style = $matches[2] ?? null;

        if (false !== strpos($mods, '+')) {
            $newLines = 0;
        }

        if (false !== strpos($mods, '^')) {
            $newLines = 2;
        }

        if (preg_match('/([>]+)/', $mods, $matches)) {
            $count = strlen($matches[1]);
            $message = str_repeat("\t", $count).$message;
        }

        if ($style !== null && $this->color) {
            if (isset($this->styles[$style])) {
                [$prefix, $style, $isError] = $this->styles[$style];

                if ($newLines) {
                    $message = $prefix.$message;
                }
            } else {
                $style = explode('|', $style);
            }

            $style = $this->extract($style);
            $message = $this->format($message, $style[0], $style[1], ...$style[2]);
        }

        if (false !== strpos($mods, '!')) {
            $isError = true;
        }

        if (false !== strpos($mods, '!!')) {
            $isError = false;
        }

        for ($i = 0; $i < $newLines; $i++) {
            $message .= PHP_EOL;
        }

        if ($isError) {
            $channel = 'error';
        }

        return $message;
    }

    /**
     * Extract string
     */
    public function extractString(string $style): array
    {
        return $this->extract(explode('|', $style));
    }

    /**
     * Extract a stored array
     */
    public function extract(array $styles): array
    {
        $fg = $bg = false;
        $output = ['default', 'default', []];

        foreach ($styles as $style) {
            if (isset(static::FG_COLORS[$style])) {
                if (!$fg) {
                    $output[0] = $style;
                    $fg = true;
                } elseif (!$bg) {
                    $output[1] = $style;
                    $bg = true;
                }
            } elseif (isset(static::OPTIONS[$style])) {
                $output[2][] = $style;
            }
        }

        return $output;
    }
}
