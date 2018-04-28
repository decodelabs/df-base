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

class Generic implements IContainer
{
    protected $bindings = [];
    protected $providers = [];
    protected $aliases = [];
    protected $events;


    /**
     * Take a list of provider types and register
     */
    public function registerProviders(string ...$providers): void
    {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }

    /**
     * Instantiate provider and register
     */
    public function registerProvider(string $provider): void
    {
        if (!class_exists($provider, true)) {
            throw df\Error::{'EImplementation,ENotFound'}(
                'Service provider '.$provider.' could not be found'
            );
        }

        $this->registerProviderInstance(new $provider());
    }

    /**
     * Register provider instance
     */
    public function registerProviderInstance(core\IServiceProvider $provider): void
    {
        $types = $provider::getProvidedServices();

        foreach ($types as $type) {
            if (isset($this->bindings[$type])) {
                continue;
            }
            
            $this->providers[$type] = $provider;

            if ($alias = Binding::typeToAlias($type)) {
                $this->registerAlias($type, $alias);
            }
        }
    }


    /**
     * Get list of registered providers
     */
    public function getProviders(): array
    {
        return $this->providers;
    }


    /**
     * Bind a concrete type or instance to interface
     */
    public function bind(string $type, $target=null): IBinding
    {
        $binding = new Binding($this, $type, $target);
        $type = $binding->getType();

        if ($oldBinding = $this->bindings[$type] ?? null) {
            $this->remove($type);
        }

        $this->bindings[$type] = $binding;
        unset($this->providers[$type]);

        if ($oldBinding) {
            $this->triggerAfterRebinding($binding);
        }

        return $binding;
    }

    /**
     * Only bind if it's not bound already
     */
    public function bindOnce(string $type, $target=null, callable $callback=null): IBinding
    {
        if (isset($this->bindings[$type])) {
            return $this->bindings[$type];
        }

        $binding = $this->bind($type, $target);

        if ($callback) {
            $callback($binding, $this);
        }

        return $binding;
    }

    /**
     * Add binding as part of a group
     */
    public function bindToGroup(string $type, $target=null): IBinding
    {
        $oldBinding = null;

        if (isset($this->bindings[$type])) {
            $group = $this->bindings[$type];

            if (!$group instanceof Group) {
                $oldBinding = $group;
                $group = new Group($this, $type);
                $group->addBinding($oldBinding);
                $this->remove($type);
            }
        } else {
            $group = new Group($this, $type);
        }

        $binding = new Binding($this, $type, $target);
        $group->addBinding($binding);
        $this->bindings[$type] = $group;
        unset($this->providers[$type]);

        return $binding;
    }


    /**
     * Bind a singleton concrete type
     */
    public function bindShared(string $type, $target=null): IBinding
    {
        return $this->bind($type, $target)->setShared(true);
    }

    /**
     * Bind singleton only if it's not bound already
     */
    public function bindSharedOnce(string $type, $target=null, callable $callback=null): IBinding
    {
        if (isset($this->bindings[$type])) {
            return $this->bindings[$type];
        }

        $binding = $this->bindShared($type, $target);

        if ($callback) {
            $callback($binding, $this);
        }

        return $binding;
    }

    /**
     * Add singleton binding as group
     */
    public function bindSharedToGroup(string $type, $target=null): IBinding
    {
        return $this->bindToGroup($type, $target)->setShared(true);
    }


    /**
     * Set an alias for an existing binding
     */
    public function alias(string $type, string $alias): IContainer
    {
        $this->getBinding($type)->alias($alias);
        return $this;
    }

    /**
     * Retrieve the alias from binding if it exists
     */
    public function getAlias(string $type): ?string
    {
        if (isset($this->bindings[$type])) {
            return $this->bindings[$type]->getAlias();
        }

        if (false !== ($key = array_search($type, $this->aliases))) {
            return $key;
        }

        return null;
    }

    /**
     * Has an alias been used?
     */
    public function hasAlias(string $alias): bool
    {
        return isset($this->aliases[$alias]);
    }

    /**
     * Has this bound type been aliased?
     */
    public function isAliased(string $type): bool
    {
        return in_array($type, $this->aliases);
    }

    /**
     * Lookup alias
     */
    public function getAliasedType(string $alias): ?string
    {
        return $this->aliases[$alias] ?? null;
    }


    /**
     * Quietly add $alias to the reference list
     */
    public function registerAlias(string $type, string $alias): void
    {
        $this->aliases[$alias] = $type;
    }

    /**
     * Quietly remove $alias from the reference list
     */
    public function unregisterAlias(string $alias): void
    {
        unset($this->aliases[$alias]);
    }



    /**
     * Build or retrieve an instance
     */
    public function get($type)
    {
        return $this->getBinding($type)
            ->getInstance();
    }

    /**
     * Build or retrieve an instance using params
     */
    public function getWith(string $type, array $params=[]): object
    {
        return $this->getBinding($type)
            ->addParams($params)
            ->getInstance();
    }

    /**
     * Return array of bound instances
     */
    public function getGroup(string $type): array
    {
        return $this->getBinding($type)
            ->getGroupInstances();
    }

    /**
     * Loop through all group instances and call callback
     */
    public function each(string $type, callable $callback): IContainer
    {
        foreach ($this->getGroup($type) as $instance) {
            $callback($instance, $this);
        }

        return $this;
    }


    /**
     * Is this type or alias bound?
     */
    public function has($type): bool
    {
        return isset($this->bindings[$type])
            || isset($this->aliases[$type])
            || isset($this->providers[$type]);
    }


    /**
     * Remove a current binding
     */
    public function remove(string $type): IContainer
    {
        unset($this->providers[$type]);

        if (!isset($this->bindings[$type])) {
            return $this;
        }

        $binding = $this->bindings[$type];

        if (null !== ($alias = $binding->getAlias())) {
            unset($this->aliases[$alias]);
        }

        unset($this->bindings[$type]);
        return $this;
    }



    /**
     * Look up existing binding
     */
    public function getBinding(string $type): IBinding
    {
        if (isset($this->aliases[$type])) {
            $type = $this->aliases[$type];
        }

        if (isset($this->bindings[$type])) {
            return $this->bindings[$type];
        }

        if (isset($this->providers[$type])) {
            $this->providers[$type]->registerServices($this);

            if (isset($this->bindings[$type])) {
                return $this->bindings[$type];
            }
        }

        throw df\Error::{
            'ENotFound,Psr\\Container\\NotFoundExceptionInterface'
        }(
            $type.' has not been bound'
        );
    }

    /**
     * Get all binding objects
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }


    /**
     * Add a preparator to binding
     */
    public function prepareWith(string $type, callable $callback): IContainer
    {
        $this->getBinding($type)->prepareWith($callback);
        return $this;
    }

    /**
     * Add a single injection parameter
     */
    public function inject(string $type, string $name, $value): IContainer
    {
        $this->getBinding($type)->inject($name, $value);
        return $this;
    }

    /**
     * Add an array of injection parameters
     */
    public function addParams(string $type, array $params): IContainer
    {
        $this->getBinding($type)->addParams($params);
        return $this;
    }

    /**
     * Clear injected params from binding
     */
    public function clearParams(string $type): IContainer
    {
        $this->getBinding($type)->clearParams();
        return $this;
    }

    /**
     * Clear injected params from all bindings
     */
    public function clearAllParams(): IContainer
    {
        foreach ($this->bindings as $binding) {
            $binding->clearParams();
        }

        return $this;
    }



    /**
     * Reset everything
     */
    public function clear(): IContainer
    {
        $this->bindings = [];
        $this->aliases = [];

        if ($this->events) {
            $this->events->clear();
        }

        return $this;
    }


    /**
     * Force a binding to forget its shared instance
     */
    public function forgetInstance(string $type): IBinding
    {
        $this->getBinding($type)->forgetInstance();
        return $this;
    }

    /**
     * Force all bindings to forget shared instances
     */
    public function forgetAllInstances(): IContainer
    {
        foreach ($this->bindings as $binding) {
            $binding->forgetInstance();
        }

        return $this;
    }




    /**
     * Add an event handler for when instances are created
     */
    public function afterResolving(string $type, callable $callback): IContainer
    {
        $this->events()->after('resolving.'.$type, $callback);
        return $this;
    }

    /**
     * Trigger events on building a new instance
     */
    public function triggerAfterResolving(IBinding $binding, object $instance)
    {
        $type = $binding->getType();

        $this->events()->withAfter(['resolving.'.$type, 'resolving.*'], function ($events) use ($type, $instance) {
            $events->triggerAfter('resolving.'.$type, $instance, $this);
            $events->triggerAfter('resolving.*', $instance, $this);
        });
    }

    /**
     * Add an event handler for after rebinding
     */
    public function afterRebinding(string $type, callable $callback): IContainer
    {
        $this->events()->after('rebinding.'.$type, $callback);
        return $this;
    }

    /**
     * Trigger rebinding events
     */
    public function triggerAfterRebinding(IBinding $binding): void
    {
        $type = $binding->getType();

        $this->events()->withAfter(['rebinding.'.$type, 'rebinding.*'], function ($events) use ($type, $binding) {
            $instance = $binding->getInstance();

            $events->triggerAfter('rebinding.'.$type, $instance, $this);
            $events->triggerAfter('rebinding.*', $instance, $this);
        });
    }




    /**
     * Alias getBinding()
     */
    public function __get(string $type): IBinding
    {
        return $this->getBinding($type);
    }

    /**
     * Alias bind()
     */
    public function __set(string $type, $target)
    {
        return $this->bind($type, $target);
    }

    /**
     * Alias has()
     */
    public function __isset(string $type): bool
    {
        return $this->has($type);
    }

    /**
     * Alias remove()
     */
    public function __unset(string $type)
    {
        return $this->remove($type);
    }




    /**
     * Alias get()
     */
    public function offsetGet($type): object
    {
        return $this->get($type);
    }

    /**
     * Alias bind()
     */
    public function offsetSet($type, $target)
    {
        return $this->bind($type, $target);
    }

    /**
     * Alias has()
     */
    public function offsetExists($type): bool
    {
        return $this->has($type);
    }

    /**
     * Alias remove()
     */
    public function offsetUnset($type)
    {
        return $this->remove($type);
    }


    /**
     * Prepare event dispatcher
     */
    protected function events(): core\event\Dispatcher
    {
        if (!$this->events) {
            $this->events = new core\event\Dispatcher();
        }

        return $this->events;
    }


    /**
     * Normalize for dump
     */
    public function __debugInfo(): array
    {
        $output = [];

        foreach ($this->bindings as $binding) {
            $alias = $binding->getAlias() ?? $binding->getType();
            $output[$alias] = $binding->describeInstance();
        }

        return $output;
    }
}
