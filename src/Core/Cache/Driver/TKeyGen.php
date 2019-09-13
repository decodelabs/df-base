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
        }

        return [
            'normal' => $key,
            'children' => $children,
            'self' => $self
        ];
    }

    /**
     * Create basic key
     */
    protected function createKey(string $namespace, ?string $key): string
    {
        $man = $this->parseKey($namespace, $key);

        if ($man['children']) {
            throw Glitch::EInvalidArgument('Invalid cache key', null, $key);
        }

        return $this->buildKey($namespace, $man['normal']);
    }

    /**
     * Create basic key and merge with manifest
     */
    protected function inspectKey(string $namespace, ?string $key): array
    {
        $man = $this->parseKey($namespace, $key);
        $man['key'] = $this->buildKey($namespace, $man['normal']);
        return $man;
    }


    /**
     * Create an internal key
     */
    protected function createRegexKey(string $namespace, ?string $key): string
    {
        $man = $this->parseKey($namespace, $key);
        $output = $this->buildKey($namespace, $man['normal']);
        $output = '/^'.preg_quote($output);

        if ($man['self'] && $man['children']) {
            $output .= '.*';
        } elseif ($man['children']) {
            $output .= '.+';
        }

        $output .= '$/';
        return $output;
    }

    /**
     * Build key string
     */
    protected function buildKey(string $namespace, ?string $key): string
    {
        $separator = static::KEY_SEPARATOR;
        $output = $this->prefix.$separator.$namespace.$separator;

        if ($key !== null) {
            $output .= str_replace('.', $separator, $key).$separator;
        }

        return $output;
    }

    /**
     * Create an internal lock key
     */
    protected function createLockKey(string $namespace, string $key): string
    {
        $separator = static::KEY_SEPARATOR;
        $key = str_replace('.', $separator, $key);
        $output = $this->prefix.'!lock'.$separator.md5($namespace.$separator.$key);

        return $output;
    }
}
