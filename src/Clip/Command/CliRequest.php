<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Clip\Command;

use Df;

class CliRequest implements IRequest
{
    protected $path;
    protected $script;
    protected $args = [];
    protected $server = [];

    /**
     * Init
     */
    public function __construct(
        array $server=[],
        string $path=null,
        array $args=[],
        string $script=null
    ) {
        $this->server = $server;
        $this->path = $path;
        $this->args = $args;
        $this->script = $script;
    }


    /**
     * Alias withScript()
     */
    public function setScript(string $script): IRequest
    {
        return $this->withScript($script);
    }

    /**
     * Get launch script
     */
    public function getScript(): ?string
    {
        return $this->script;
    }

    /**
     * Get launch script path
     */
    public function getScriptPath(): ?string
    {
        if ($this->script === null) {
            return null;
        }

        if (false === strpos(str_replace('\\', '/', $this->script), '/')) {
            return realpath($this->script);
        }

        return $this->script;
    }

    /**
     * New instance with script set
     */
    public function withScript(string $script): IRequest
    {
        $output = clone $this;
        $output->script = $script;

        return $output;
    }



    /**
     * Alias withPath()
     */
    public function setPath(string $path): IRequest
    {
        return $this->withPath($path);
    }

    /**
     * Get task path
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Set the task path
     */
    public function withPath(string $path): IRequest
    {
        $output = clone $this;
        $output->path = trim(str_replace('\\', '/', $path), '/');

        return $output;
    }


    /**
     * Alias withCommandParams()
     */
    public function setCommandParams(array $params): IRequest
    {
        return $this->withCommandParams($params);
    }

    /**
     * Get list of command args
     */
    public function getCommandParams(): array
    {
        return $this->args;
    }

    /**
     * Lookup single command arg
     */
    public function getCommandParam(string $key): ?string
    {
        if (!isset($this->args[$key])) {
            return null;
        }

        return (string)$this->args[$key];
    }

    /**
     * Is command arg set?
     */
    public function hasCommandParam(string $key): bool
    {
        return isset($this->args[$key]);
    }

    /**
     * New instance with params set
     */
    public function withCommandParams(array $params): IRequest
    {
        $output = clone $this;
        $output->args = $params;

        return $output;
    }


    /**
     * Get $_SERVER equiv
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * Get single server param
     */
    public function getServerParam(string $key): ?string
    {
        if (!isset($this->server[$key])) {
            return null;
        }

        return (string)$this->server[$key];
    }

    /**
     * Is $key in $server?
     */
    public function hasServerParam(string $key): bool
    {
        return isset($this->server[$key]);
    }


    /**
     * Convert to string
     */
    public function __toString(): string
    {
        $output = $this->path;

        if (!empty($this->args)) {
            $output .= ' '.implode(' ', $this->args);
        }

        return $output;
    }


    /**
     * Normalize for debug
     */
    public function __debugInfo(): array
    {
        return [
            'str' => $this->__toString()
        ];
    }
}