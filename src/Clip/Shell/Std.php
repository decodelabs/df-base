<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Shell;

use Df;
use Df\Core\Io\Stream;
use Df\Clip\IShell;

class Std implements IShell
{
    use TRenderer;

    protected $in;
    protected $out;
    protected $error;

    /**
     * Build with STD streams
     */
    public function __construct()
    {
        $this->in = new Stream(STDIN);
        $this->out = new Stream(STDOUT);
        $this->error = new Stream(STDERR);
    }

    /**
     * Get available space in TTY
     */
    public function getWidth(): int
    {
        static $output;

        if (!isset($output)) {
            if (function_exists('exec')) {
                if ($this->isWindows()) {
                    if (($shell = getenv('SHELL'))
                    && preg_match('/(?:bash|zsh)(?:\.exe)?$/', $shell)
                    && getenv('TERM')) {
                        $output = (int)exec('tput cols');
                    }

                    if (!$output) {
                        $return = -1;
                        $output = array();
                        exec('mode CON', $output, $return);

                        if (0 === $return && $output) {
                            if (preg_match('/:\s*[0-9]+\n[^:]+:\s*([0-9]+)\n/', implode("\n", $output), $matches)) {
                                $output = (int)$matches[1];
                            }
                        }
                    }
                } else {
                    if (!($output = (int) getenv('COLUMNS'))) {
                        $size = exec('/usr/bin/env stty size 2>/dev/null');

                        if ($size !== '' && preg_match('/[0-9]+ ([0-9]+)/', $size, $matches)) {
                            $output = (int)$matches[1];
                        }

                        if (!$output) {
                            if (getenv('TERM')) {
                                $output = (int)exec('/usr/bin/env tput cols 2>/dev/null');
                            }
                        }
                    }
                }
            }

            if (!$output) {
                $output = 80;
            }
        }

        return $output;
    }

    /**
     * Is this actually a TTY?
     */
    public function canColor(): bool
    {
        static $output;

        if (!isset($output)) {
            $stream = $this->out->getResource();

            if (function_exists('stream_isatty') && !@stream_isatty($stream)) {
                $output = false;
            } elseif ($this->isWindows()) {
                if (function_exists('sapi_windows_vt100_support')) {
                    $hasVt100 = @sapi_windows_vt100_support($stream);
                } else {
                    $hasVt100 = '10.0.10586' === PHP_WINDOWS_VERSION_MAJOR.'.'.PHP_WINDOWS_VERSION_MINOR.'.'.PHP_WINDOWS_VERSION_BUILD;
                }

                $output = $hasVt100
                    || getenv('ANSICON') !== false
                    || getenv('ConEmuANSI') === 'ON'
                    || getenv('TERM') === 'xterm';
            } else {
                $output = function_exists('posix_isatty') && @posix_isatty($stream);
            }
        }

        return $output;
    }

    /**
     * Is this windows?
     */
    private function isWindows(): bool
    {
        static $output;

        if (!isset($output)) {
            $output = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || DIRECTORY_SEPARATOR === '\\';
        }

        return $output;
    }




    /**
     * Write raw to stream
     */
    public function write(string $message): void
    {
        $this->out->write($message);
    }

    /**
     * Write line to stream
     */
    public function writeLine(?string $message=null): void
    {
        if ($message !== null) {
            $this->out->write($message);
        }

        $this->out->write(PHP_EOL);
    }


    /**
     * Write raw to error
     */
    public function writeError(string $message): void
    {
        $this->error->write($message);
    }

    /**
     * Write line to error
     */
    public function writeErrorLine(?string $message=null): void
    {
        if ($message !== null) {
            $this->error->write($message);
        }

        $this->error->write(PHP_EOL);
    }


    /**
     * Read chunk from input
     */
    public function read(int $size): ?string
    {
        return $this->in->read($size);
    }

    /**
     * Read line from input
     */
    public function readLine(): ?string
    {
        return $this->in->readLine();
    }
}
