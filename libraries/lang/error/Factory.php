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
    const STANDARD = [
        'ELogic' => [
            'type' => 'LogicException'
        ],
            'EBadFunctionCall' => [
                'extend' => 'ELogic',
                'type' => 'BadFunctionCallException'
            ],
                'EBadMethodCall' => [
                    'extend' => 'EBadFunctionCall',
                    'type' => 'BadMethodCallException'
                ],

            'EDomain' => [
                'extend' => 'ELogic',
                'type' => 'DomainException'
            ],
            'EInvalidArgument' => [
                'extend' => 'ELogic',
                'type' => 'InvalidArgumentException'
            ],
            'ELength' => [
                'extend' => 'ELogic',
                'type' => 'LengthException'
            ],
            'EOutOfRange' => [
                'extend' => 'ELogic',
                'type' => 'OutOfRangeException'
            ],

            'EDefinition' => [
                'extend' => 'ELogic'
            ],
            'EImplementation' => [
                'extend' => 'ELogic'
            ],
                'ENotImplemented' => [
                    'extend' => 'EImplementation',
                    'http' => 501
                ],

            'EUnsupported' => [
                'extend' => 'ELogic'
            ],


        'ERuntime' => [
            'type' => 'RuntimeException'
        ],
            'EOutOfBounds' => [
                'extend' => 'ERuntime',
                'type' => 'OutOfBoundsException'
            ],
            'EOverflow' => [
                'extend' => 'ERuntime',
                'type' => 'OverflowException'
            ],
            'ERange' => [
                'extend' => 'ERuntime',
                'type' => 'RangeException'
            ],
            'EUnderflow' => [
                'extend' => 'ERuntime',
                'type' => 'UnderflowException'
            ],
            'EUnexpectedValue' => [
                'extend' => 'ERuntime',
                'type' => 'UnexpectedValueException'
            ],

            'EBadRequest' => [
                'extend' => 'ERuntime',
                'http' => 400
            ],
            'EUnauthorized' => [
                'extend' => 'ERuntime',
                'http' => 401
            ],
            'EForbidden' => [
                'extend' => 'EUnauthorized',
                'http' => 403
            ],
            'ENotFound' => [
                'extend' => 'ERuntime',
                'http' => 404
            ],
            'EComponentUnavailable' => [
                'extend' => 'ERuntime'
            ],
            'EServiceUnavailable' => [
                'extend' => 'ERuntime',
                'http' => 503
            ]
    ];

    const REWIND = 4;

    private static $instances = [];

    protected $type;
    protected $params = [];

    protected $namespace;

    protected $interfaces = [];
    protected $traits = [];

    protected $exceptionDef;
    protected $interfaceDefs = [];


    /**
     * Generate a context specific, message oriented throwable error
     */
    public static function create(?string $type, array $interfaces=[], $message, ?array $params=[], $data=null): df\IError
    {
        if (is_array($message)) {
            $params = $message;
            $message = $message['message'] ?? 'Undefined error';
        }

        if ($params === null) {
            $params = [];
        }

        if ($data !== null) {
            $params['data'] = $data;
        }

        return (new self($type, $params))
            ->build($message, $interfaces);
    }


    protected function __construct(?string $type, array $params=[])
    {
        $this->type = $type;
        $this->params = $params;
    }



    /**
     * Build exception object
     */
    protected function build(string $message, array $interfaces): df\IError
    {
        $this->params['rewind'] = $rewind = max((int)($this->params['rewind'] ?? 0), 0);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $rewind + static::REWIND);
        $key = $rewind + static::REWIND - 1;

        if (isset($this->params['namespace'])) {
            $this->namespace = $this->params['namespace'];
            unset($this->params['namespace']);
        } elseif (isset($trace[$key])) {
            $this->namespace = $trace[$key]['class'] ?? null;

            if (!empty($this->namespace)) {
                if (false !== strpos($this->namespace, 'class@anon')) {
                    $this->namespace = '\\df';
                } else {
                    $parts = explode('\\', $this->namespace);
                    $className = array_pop($parts);
                    $this->namespace = implode('\\', $parts);
                }
            }
        }

        if (empty($this->namespace)) {
            $this->namespace = '\\df';
        }

        $this->buildDefinitions($interfaces);

        foreach ($this->interfaceDefs as $interface => $def) {
            if (!interface_exists('\\'.$interface)) {
                eval($def);
            }
        }

        $hash = md5($this->exceptionDef);

        if (!isset(self::$instances[$hash])) {
            self::$instances[$hash] = eval($this->exceptionDef);
        }

        $trace = array_pop($trace);

        if (!isset($this->params['file'])) {
            $this->params['file'] = $trace['file'];
        }

        if (!isset($this->params['line'])) {
            $this->params['line'] = $trace['line'];
        }

        return new self::$instances[$hash]($message, $this->params);
    }


    protected function buildDefinitions(array $interfaces): void
    {
        $namespace = ltrim($this->namespace, '\\');
        $namespaces = [$namespace];
        $directType = null;
        $this->traits[] = 'df\\lang\\error\\TError';

        // Create initial interface list
        foreach ($interfaces as $i => $interface) {
            if (false !== strpos($interface, '/')) {
                $interface = 'df\\'.str_replace('/', '\\', ltrim($interface, '/'));
            }

            $interface = ltrim($interface, '\\');

            if (false === strpos($interface, '\\')) {
                $interface = $namespace.'\\'.$interface;
            }

            if (null !== ($ns = $this->listInterface($interface))) {
                $namespaces[] = $ns;
            }
        }


        // Create inheritance trees
        foreach (array_unique($namespaces) as $namespace) {
            $this->extractNamespaceInterfaces($namespace);
        }


        // Sort inheritance list
        foreach ($this->interfaces as $interface => $info) {
            if ($info !== null) {
                $this->defineInterface($interface, $info);
            }
        }

        // Ensure defaults
        if ($this->type === null) {
            $this->type = \Exception::class;
        }

        if (empty($this->interfaces)) {
            $this->interfaces['df\\IError'] = [];
        }


        // Build class def
        $this->exceptionDef = 'return new class(\'\') extends '.$this->type;

        if (!empty($this->interfaces)) {
            $this->exceptionDef .= ' implements '.implode(',', array_keys($this->interfaces));
        }

        $this->exceptionDef .= ' {';

        foreach (array_unique($this->traits) as $trait) {
            $this->exceptionDef .= 'use '.$trait.';';
        }

        $this->exceptionDef .= '};';
    }



    /**
     * Add interface info to class extend list
     */
    protected function listInterface(string $interface): ?string
    {
        $parts = explode('\\', $interface);
        $name = array_pop($parts);
        $output = null;

        if ($name !== 'IError' && !preg_match('/^(E)[A-Z][a-zA-Z0-9_]+$/', $name)) {
            return null;
        }

        $output = implode('\\', $parts);

        if (isset(static::STANDARD[$name])) {
            $standard = static::STANDARD[$name];

            if (isset($standard['extend'])) {
                $standard['extend'] = ['df\\'.$standard['extend']];
            }

            $this->interfaces[$interface] = $standard;

            if ($this->type === null && isset($standard['type'])) {
                $this->type = $standard['type'];
            }

            if (!isset($this->params['http']) && isset($standard['http'])) {
                $this->params['http'] = $standard['http'];
            }
        } elseif (!isset($this->interfaces[$interface])) {
            $this->interfaces[$interface] = [];
        }

        if ($name === 'IError') {
            array_pop($parts);
        }

        $extend = implode('\\', $parts).'\\IError';

        if (count($parts) > 1) {
            $this->interfaces[$interface]['extend'][] = $extend;
        }

        return $output;
    }


    /**
     * Create an interface tree back down to df ns root
     */
    protected function extractNamespaceInterfaces(string $namespace): void
    {
        $parts = explode('\\', $namespace);
        $parts = array_slice($parts, 1, 3);
        $parent = 'df';

        foreach ($parts as $part) {
            $first = $parent == 'df';
            $ins = $parent.'\\'.$part;
            $interface = $ins.'\\IError';

            $this->listInterface($interface);
            $parent = $ins;
        }
    }


    /**
     * Recursively define interfaces, adding in inherited parents
     */
    protected function defineInterface(string $interface, array $info): void
    {
        $parent = '\\df\\IError';

        if (isset($info['extend'])) {
            $parent = [];

            foreach ($info['extend'] as $extend) {
                $parent[] = '\\'.$extend;
                $parts = explode('\\', $extend);
                $name = array_pop($parts);

                if (isset($this->interfaces[$extend])) {
                    $inner = $this->interfaces[$extend];
                    unset($this->interfaces[$extend]);
                    $this->defineInterface($extend, $inner);
                } elseif (isset(static::STANDARD[$name])) {
                    $standard = static::STANDARD[$name];

                    if (isset($standard['extend'])) {
                        $standard['extend'] = ['df\\'.$standard['extend']];
                    }

                    $this->defineInterface($extend, $standard);
                }
            }

            $parent = implode(',', $parent);
        }

        $parts = explode('\\', $interface);
        $name = array_pop($parts);
        $traitName = implode('\\', $parts).'\\T'.substr($name, 1);

        if (trait_exists($traitName, true)) {
            $this->traits[] = $traitName;
        }

        $this->interfaceDefs[$interface] = 'namespace '.implode($parts, '\\').';interface '.$name.' extends '.$parent.' {}';
    }
}
