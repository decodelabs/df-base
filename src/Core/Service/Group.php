<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Service;

use Df\Core\Service\Container;
use Df\Core\Service\Binding;

use DecodeLabs\Exceptional;

class Group extends Binding
{
    protected $bindings = [];

    public function __construct(Container $container, string $type)
    {
        parent::__construct($container, $type, null, false, true);
        unset($this->params, $this->target);
    }



    /**
     * Noop
     */
    public function setTarget($target): Binding
    {
        throw Exceptional::Implementation(
            'setTarget is not used for groups'
        );
    }

    /**
     * Noop
     */
    public function getTarget()
    {
        throw Exceptional::Implementation(
            'getTarget is not used for groups'
        );
    }

    /**
     * Noop
     */
    public function setFactory(\Closure $factory)
    {
        throw Exceptional::Implementation(
            'setFactory is not used for groups'
        );
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
    public function addBinding(Binding $binding): Binding
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
    public function clearPreparators(): Binding
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
    public function inject(string $name, $value): Binding
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
    public function addParams(array $params): Binding
    {
        foreach ($this->bindings as $binding) {
            foreach ($params as $key => $value) {
                $binding->inject($key, $value);
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
    public function removeParam(string $name): Binding
    {
        foreach ($this->bindings as $binding) {
            $binding->removeParam($name);
        }

        return $this;
    }

    /**
     * Get rid of all injected params
     */
    public function clearParams(): Binding
    {
        foreach ($this->bindings as $binding) {
            $binding->clearParams();
        }

        return $this;
    }



    /**
     * Noop
     */
    public function setInstance(object $instance): Binding
    {
        throw Exceptional::Implementation(
            'setFactory is not used for groups'
        );
    }

    /**
     * Get rid of current shared instance
     */
    public function forgetInstance(): Binding
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

        throw Exceptional::Runtime(
            'No available bindings'
        );
    }

    /**
     * Create a new instance
     */
    public function newInstance(): object
    {
        foreach ($this->bindings as $binding) {
            return $binding->newInstance();
        }

        throw Exceptional::Runtime(
            'No available bindings'
        );
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

    /**
     * Create a simple text representation of instance or factory
     */
    public function describeInstance()
    {
        $output = [];

        foreach ($this->bindings as $binding) {
            $output[] = $binding->describeInstance();
        }

        return $output;
    }
}
