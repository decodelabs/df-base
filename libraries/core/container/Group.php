<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\container;

use df;
use df\core;
use df\core\IContainer;

class Group extends Binding
{
    protected $bindings = [];

    public function __construct(IContainer $container, string $type)
    {
        $this->container = $container;

        if (!interface_exists($type, true) && !class_exists($type, true)) {
            throw df\Error::EInvalidArgument(
                'Binding type must be a valid interface'
            );
        }

        $this->type = $type;
        unset($this->params);
    }



    /**
     * Noop
     */
    public function setTarget($target): IBinding
    {
        throw df\Error::EImplementation('setTarget is not used for groups');
    }

    /**
     * Noop
     */
    public function setFactory(\Closure $factory)
    {
        throw df\Error::EImplementation('setFactory is not used for groups');
    }


    /**
     * Generate a looper factory
     */
    public function getFactory(): ?\Closure
    {
        return function (): array {
            $output = [];

            foreach ($this->bindings as $binding) {
                $output[] = $binding->getInstance();
            }

            return $output;
        };
    }



    /**
     * Add a binding to the list
     */
    public function addBinding(IBinding $binding): IBinding
    {
        $this->bindings[] = $binding;
        return $this;
    }

    /**
     * Get list of bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }


    /**
     * Are there any registered preparator callbacks?
     */
    public function hasPreparators(): bool
    {
        if (parent::hasPreparators()) {
            return true;
        }

        foreach ($this->bindings as $binding) {
            if ($binding->hasPreparators()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove all preparators
     */
    public function clearPreparators(): IBinding
    {
        $this->preparators = [];

        foreach ($this->bindings as $binding) {
            $binding->clearPreparators();
        }

        return $this;
    }



    /**
     * Add an injected call parameter
     */
    public function inject(string $name, $value): IBinding
    {
        foreach ($this->bindings as $binding) {
            $binding->inject($name, $value);
        }

        return $this;
    }

    /**
     * Look up an injected param
     */
    public function getParam(string $name)
    {
        foreach ($this->bindings as $binding) {
            if ($binding->hasParam($name)) {
                return $binding->getParam($name);
            }
        }

        return null;
    }

    /**
     * Add a list of injected params
     */
    public function addParams(array $params): IBinding
    {
        foreach ($this->bindings as $binding) {
            foreach ($params as $key => $value) {
                $binding->inject($name, $value);
            }
        }

        return $this;
    }

    /**
     * Has a specific parameter been injected?
     */
    public function hasParam(string $name): bool
    {
        foreach ($this->bindings as $binding) {
            if ($binding->hasParam($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get rid of an injected param
     */
    public function removeParam(string $name): IBinding
    {
        foreach ($this->bindings as $binding) {
            $binding->removeParam($name);
        }

        return $this;
    }

    /**
     * Get rid of all injected params
     */
    public function clearParams(): IBinding
    {
        foreach ($this->bindings as $binding) {
            $binding->clearParams();
        }

        return $this;
    }



    /**
     * Noop
     */
    public function setInstance(object $instance): IBinding
    {
        throw df\Error::EImplementation('setFactory is not used for groups');
    }

    /**
     * Get rid of current shared instance
     */
    public function forgetInstance(): IBinding
    {
        foreach ($this->bindings as $binding) {
            $binding->forgetInstance();
        }

        return $this;
    }

    /**
     * Build new or return current instance
     */
    public function getInstance(): object
    {
        foreach ($this->bindings as $binding) {
            return $binding->getInstance();
        }
    }

    /**
     * Create a new instance
     */
    public function newInstance(): object
    {
        foreach ($this->bindings as $binding) {
            return $binding->newInstance();
        }
    }

    /**
     * Wrap instance in array
     */
    public function getGroupInstances(): array
    {
        $output = [];

        foreach ($this->bindings as $binding) {
            $output[] = $binding->getInstance();
        }

        return $output;
    }
}
