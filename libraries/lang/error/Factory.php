<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\error;

use df;
use df\core;

class Factory
{
    private static $instances = [];

    /**
     * Generate a context specific, message oriented throwable error
     */
    public static function create(?string $type, $message, array $params=[], array $interfaces=[]): IError
    {
        if (is_array($message)) {
            $params = $message;
            $message = $message['message'] ?? 'Undefined error';
        }

        $params['rewind'] = $rewind = max((int)($params['rewind'] ?? 0), 0);
        $activeRewind = $rewind + 3;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $activeRewind);
        $namespace = null;
        $activeRewind--;

        if(isset($trace[$activeRewind])) {
            $namespace = $trace[$activeRewind]['class'] ?? null;
        }

        $activeRewind--;

        if (isset($params['namespace'])) {
            $namespace = $params['namespace'];
            unset($params['namespace']);
        } else {
            if (!empty($namespace)) {
                if (false !== strpos($namespace, 'class@anon')) {
                    $namespace =  '\\df';
                } else {
                    $namespace = explode('\\', $namespace);
                    $className = array_pop($namespace);
                    $namespace = implode('\\', $namespace);
                }
            } else {
                $namespace = '\\df';
            }
        }

        $defs = static::buildDefinitions($type, $interfaces, $namespace);
        $exceptionDef = $defs['@exception'];
        unset($defs['@exception']);

        foreach ($defs as $def) {
            eval($def);
        }

        $hash = md5($exceptionDef);

        if (!isset(self::$instances[$hash])) {
            self::$instances[$hash] = eval($exceptionDef);
        }

        $trace = array_pop($trace);

        if (!isset($params['file'])) {
            $params['file'] = $trace['file'];
        }

        if (!isset($params['line'])) {
            $params['line'] = $trace['line'];
        }

        return new self::$instances[$hash]($message, $params);
    }


    /**
     * Create eval definitions for the class and interfaces
     */
    public static function buildDefinitions(?string $type, array $interfaces, string $namespace): array
    {
        $traits = $defs = [];
        $namespace = ltrim($namespace, '\\');
        $namespaces = [$namespace];

        if(!interface_exists('\\df\\IError')) {
            $defs['df\\IError'] = 'namespace df;interface IError extends \\df\\lang\\error\\IError {};';
        }

        $traits[] = 'df\\lang\\error\\TError';

        if (!empty($interfaces)) {
            foreach ($interfaces as $i => $interface) {
                if (false !== strpos($interface, '/')) {
                    $interface = 'df\\'.str_replace('/', '\\', ltrim($interface, '/'));
                }

                $interface = ltrim($interface, '\\');

                if (false === strpos($interface, '\\')) {
                    $interface = $namespace.'\\'.$interface;
                } else {
                    $parts = explode('\\', $interface);
                    $test = '\\df\\lang\\'.array_pop($parts);
                }

                if (!interface_exists($interface, true)) {
                    $parts = explode('\\', $interface);
                    $name = array_pop($parts);

                    if (($parts[1] ?? 'lang') !== 'lang') {
                        $namespaces[] = implode('\\', $parts);
                    }

                    $defs[$interface] = 'namespace '.implode($parts, '\\').';interface '.$name.' extends \\df\\IError {}';
                }

                if ($type === null) {
                    $baseName = '\\'.substr($name, 1).'Exception';

                    if (class_exists($baseName)) {
                        $type = $baseName;
                    }
                }

                $interfaces[$i] = $interface;
                $parts = explode('\\', $interface);
                $name = array_pop($parts);

                if (!preg_match('/^E[A-Z][a-zA-Z0-9_]+$/', $name)) {
                    unset($interfaces[$i]);
                    continue;
                }

                $traitName = implode('\\', $parts).'\\T'.substr($name, 1);

                if (trait_exists($traitName, true)) {
                    $traits[] = $traitName;
                }
            }
        }

        foreach (static::extractNamespaceInterfaces(...array_unique($namespaces)) as $interface => $def) {
            $interfaces[] = $interface;

            if ($def !== null) {
                $defs[$interface] = $def;
            }
        }

        if(empty($interfaces)) {
            $interfaces[] = IError::class;
        }

        if ($type === null) {
            $type = \Exception::class;
        }

        $definition = 'return new class(\'\') extends '.$type;

        if (!empty($interfaces)) {
            $definition .= ' implements '.implode(',', array_unique($interfaces));
        }

        $definition .= ' {';

        foreach ($traits as $trait) {
            $definition .= 'use '.$trait.';';
        }

        $definition .= '};';
        $defs['@exception'] = $definition;

        return $defs;
    }



    /**
     * Create an interface tree for the current namespace
     */
    private static function extractNamespaceInterfaces(string ...$namespaces): array
    {
        $extra = [];

        foreach ($namespaces as $namespace) {
            $parts = explode('\\', $namespace);
            $parts = array_slice($parts, 1, 3);
            $parent = 'df';

            foreach ($parts as $part) {
                $first = $parent == 'df';
                $ins = $parent.'\\'.$part;
                $interface = $ins.'\\IError';
                $interfaceDef = null;

                if (!interface_exists($interface, true)) {
                    $base = $first ? '\\df\\IError' : '\\'.$parent.'\\IError';
                    $interfaceDef = 'namespace '.$ins.';interface IError extends '.$base.' {}';
                }

                $parent = $ins;
                $extra[$interface] = $interfaceDef;
            }
        }

        return $extra;
    }
}
