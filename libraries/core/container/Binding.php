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

use Psr\Container\NotFoundExceptionInterface;

class Binding implements IBinding
{
    protected $type;
    protected $alias;

    protected $factory;
    protected $shared = false;
    protected $instance;

    protected $preparators = [];
    protected $params = [];

    protected $container;


    /**
     * Create new instance referencing base container
     */
    public function __construct(IContainer $container, string $type, $target)
    {
        $this->container = $container;

        if (!interface_exists($type, true) && !class_exists($type, true)) {
            throw df\Error::EInvalidArgument(
                'Binding type must be a valid interface'
            );
        }

        $this->type = $type;
        $this->setTarget($target);
    }


    /**
     * Get referenced base container
     */
    public function getContainer(): IContainer
    {
        return $this->container;
    }

    /**
     * Get interface type
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Prepare factory or instance
     */
    public function setTarget($target): IBinding
    {
        if ($target === null) {
            $target = $this->type;
        }

        if (!$target instanceof \Closure) {
            if (is_object($target)) {
                $this->setInstance($target);
                $target = get_class($target);
            }

            if (is_string($target)) {
                $target = function () use ($target) {
                    return $this->buildType($target);
                };
            } else {
                throw df\Error::{
                    'EInvalidArgument,Psr\\Container\\NotFoundExceptionInterface'
                }(
                    'Binding target for '.$this->type.' cannot be converted to a factory'
                );
            }
        }

        return $this->setFactory($target);
    }

    /**
     * Set resolver factory closure
     */
    public function setFactory(\Closure $factory)
    {
        $oldFactory = $this->factory;
        $this->factory = $factory;

        if ($oldFactory !== null) {
            $this->container->triggerAfterRebinding($this);
        }

        return $this;
    }

    /**
     * Get resolver factory closure if set
     */
    public function getFactory(): ?\Closure
    {
        return $this->factory;
    }


    /**
     * Set an alias for the binding
     */
    public function alias(string $alias): IBinding
    {
        if (false !== strpos($alias, '\\')) {
            throw df\Error::{
                'EInvalidArgument,Psr\Container\ContainerExceptionInterface'
            }(
                'Aliases must not contain \\ character',
                null,
                $alias
            );
        }

        if ($alias === $this->alias) {
            return $this;
        }

        if ($this->container->hasAlias($alias)) {
            throw df\Error::{
                'ELogic,Psr\Container\ContainerExceptionInterface'
            }(
                'Alias "'.$alias.'" has already been bound'
            );
        }

        if ($this->alias !== null) {
            $this->container->unregisterAlias($this->alias);
        }

        $this->alias = $alias;
        $this->container->registerAlias($this->type, $alias);

        return $this;
    }

    /**
     * Get alias if it's been set
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Has an alias been set?
     */
    public function hasAlias(): bool
    {
        return $this->alias !== null;
    }

    /**
     * Unregister the alias with the container
     */
    public function removeAlias(): IBinding
    {
        if ($this->alias !== null) {
            $this->container->unregisterAlias($this->alias);
        }

        $this->alias = null;
        return $this;
    }


    /**
     * Is this item singleton?
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * Make this item a singleton
     */
    public function setShared(bool $shared): IBinding
    {
        $this->shared = $shared;
        return $this;
    }


    /**
     * Add a preparator callback
     */
    public function prepareWith(callable $callback): IBinding
    {
        $this->preparators[spl_object_id($callback)] = $callback;
        return $this;
    }

    /**
     * Are there any registered preparator callbacks?
     */
    public function hasPreparators(): bool
    {
        return !empty($this->preparators);
    }

    /**
     * Remove all preparators
     */
    public function clearPreparators(): IBinding
    {
        $this->preparators = [];
        return $this;
    }


    /**
     * Add an injected call parameter
     */
    public function inject(string $name, $value): IBinding
    {
        $this->params[ltrim($name, '$')] = $value;
        return $this;
    }

    /**
     * Get provided injected parameter
     */
    public function getParam(string $name)
    {
        return $this->params[ltrim($name, '$')] ?? null;
    }

    /**
     * Add a list of injected params
     */
    public function addParams(array $params): IBinding
    {
        foreach ($params as $key => $value) {
            $this->inject($name, $value);
        }

        return $this;
    }

    /**
     * Has a specific parameter been injected?
     */
    public function hasParam(string $name)
    {
        return array_key_exists(ltrim($name, '$'), $this->params);
    }

    /**
     * Get rid of an injected param
     */
    public function removeParam(string $name): IBinding
    {
        unset($this->params[ltrim($name, '$')]);
        return $this;
    }

    /**
     * Get rid of all injected params
     */
    public function clearParams(): IBinding
    {
        $this->params = [];
    }


    /**
     * Manually set a shared instance
     */
    public function setInstance(object $instance): IBinding
    {
        $this->instance = $this->prepareInstance($instance);
        return $this;
    }

    /**
     * Get rid of current shared instance
     */
    public function forgetInstance(): IBinding
    {
        $this->instance = null;
        return $this;
    }

    /**
     * Build new or return current instance
     */
    public function getInstance(): object
    {
        if ($this->instance) {
            $output = $this->instance;
        } else {
            $output = $this->newInstance();
            $output = $this->prepareInstance($output);

            if ($this->shared) {
                $this->instance = $output;
            }
        }

        return $output;
    }

    /**
     * Create a new instance
     */
    public function newInstance(): object
    {
        return $this->factory->__invoke($this->container);
    }

    /**
     * Run instance through preparators
     */
    protected function prepareInstance(object $instance): object
    {
        foreach ($this->preparators as $callback) {
            $instance = $callback($instance, $this);
            // TODO: check instance is still of $type
        }

        if (!$instance instanceof $this->type) {
            throw df\Error::{
                'ELogic,Psr\\Container\\ContainerExceptionInterface'
            }(
                'Binding instance does not implement type '.$this->type,
                null,
                $instance
            );
        }

        $this->container->triggerAfterResolving($this, $instance);
        return $instance;
    }


    /**
     * Build an instanceof $type
     */
    protected function buildType(string $type): object
    {
        $reflector = new \ReflectionClass($type);

        if (!$reflector->isInstantiable()) {
            throw df\Error::{
                'ELogic,Psr\\Container\\ContainerExceptionInterface'
            }(
                'Binding target '.$type.' cannot be instantiated'
            );
        }

        if (!$constructor = $reflector->getConstructor()) {
            return $reflector->newInstance();
        }

        $params = $constructor->getParameters();
        $args = [];

        foreach ($params as $param) {
            if (array_key_exists($param->name, $this->params)) {
                $args[] = $this->params[$param->name];
                continue;
            }

            if (null !== ($class = $param->getClass())) {
                try {
                    $args[] = $this->container->get($class->name);
                } catch (NotFoundExceptionInterface $e) {
                    if ($param->isOptional()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw df\Error::{
                    'ELogic,Psr\\Container\\ContainerExceptionInterface'
                }(
                    'Binding target '.$type.' cannot be instantiated'
                );
            }
        }

        return $reflector->newInstanceArgs($args);
    }


    /**
     * Add a resolver event handler
     */
    public function afterResolving(callable $callback): IBinding
    {
        $this->container->afterResolving($this->type, $callback);
        return $this;
    }

    /**
     * Add a rebind event handler
     */
    public function afterRebinding(callable $callback): IBinding
    {
        $this->container->afterRebinding($this->type, $callback);
        return $this;
    }



    /**
     * Get debug info
     */
    public function __debugInfo(): array
    {
        return [
            'type' => $this->type,
            'alias' => $this->alias,
            'shared' => $this->shared
        ];
    }
}
