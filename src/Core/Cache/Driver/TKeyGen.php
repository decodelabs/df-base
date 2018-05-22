<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df;

trait TKeyGen
{
    protected $prefix;

    /**
     * Init
     */
    public function __construct()
    {
        $this->generatePrefix();
    }

    /**
     * Create a unique prefix
     */
    protected function generatePrefix(): void
    {
        $this->prefix = base64_encode(pack('H*', md5(__FILE__)));
    }


    /**
     * Create an internal key
     */
    protected function createKey(string $namespace, ?string $key, bool $regex=false): string
    {
        $all = false;
        $output = $this->prefix.'::'.$namespace.'::';

        if ($key !== null) {
            if (substr($key, -1) == '*') {
                $all = '.*';
                $key = substr($key, 0, -1);

                if (substr($key, -1) == '.') {
                    $all = '.+';
                    $key = substr($key, 0, -1);
                }
            }

            $key = str_replace('.', '::', $key);
            $output .= $key.'::';
        }

        if ($regex) {
            $output = '/^'.preg_quote($output);
        }

        if ($all) {
            $output .= $all;
        }

        if ($regex) {
            $output .= '$/';
        }

        return $output;
    }

    /**
     * Create an internal lock key
     */
    protected function createLockKey(string $namespace, string $key, bool $regex=false): string
    {
        $key = str_replace('.', '::', $key);
        $output = $this->prefix.'::'.$namespace.'::!lock:'.$key.'::';

        if ($regex) {
            $output = '/'.preg_quote($output).'/';
        }

        return $output;
    }
}
