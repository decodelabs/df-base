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
     * Generate list of keys to delete
     */
    protected function parseKey(string $namespace, ?string $key): array
    {
        $separator = static::KEY_SEPARATOR ?? '::';
        $output = $this->prefix.$separator.$namespace.$separator;
        $children = false;
        $self = true;

        if ($key !== null) {
            if (substr($key, -1) == '*') {
                $children = true;
                $key = substr($key, 0, -1);

                if (substr($key, -1) == '.') {
                    $self = false;
                    $key = substr($key, 0, -1);
                }
            }

            $key = str_replace('.', $separator, $key);
            $output .= $key.$separator;
        }

        return [
            'key' => $output,
            'children' => $children,
            'self' => $self
        ];
    }

    /**
     * Create an internal key
     */
    protected function createKey(string $namespace, ?string $key, bool $regex=false): string
    {
        $separator = static::KEY_SEPARATOR ?? '::';
        $all = false;
        $output = $this->prefix.$separator.$namespace.$separator;

        if ($key !== null) {
            if (substr($key, -1) == '*') {
                $all = '.*';
                $key = substr($key, 0, -1);

                if (substr($key, -1) == '.') {
                    $all = '.+';
                    $key = substr($key, 0, -1);
                }
            }

            $key = str_replace('.', $separator, $key);
            $output .= $key.$separator;
        }

        if ($regex) {
            $output = '/^'.preg_quote($output);
        }

        if ($all) {
            if (!$regex) {
                throw Df\Error::EInvalidArgument('Invalid cache key', null, $key);
            }

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
        $separator = static::KEY_SEPARATOR ?? '::';
        $key = str_replace('.', $separator, $key);
        $output = $this->prefix.$separator.$namespace.$separator.'!lock:'.$key.$separator;

        if ($regex) {
            $output = '/'.preg_quote($output).'/';
        }

        return $output;
    }
}
