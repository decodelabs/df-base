<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Service\Container;

use Df\Core\Service\Container;
use Df\Core\Service\Binding;
use Df\Core\Service\Provider;
use Df\Core\Service\Group;
use Df\Core\Event\Dispatcher;

use DecodeLabs\Exceptional;

use Psr\Container\NotFoundExceptionInterface;

class Generic implements Container
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
            throw Exceptional::{'Implementation,NotFound'}(
                'Service provider '.$provider.' could not be found'
            );
        }

        $this->registerProviderInstance(new $provider());
    }

    /**
     * Register provider instance
     */
    public function registerProviderInstance(Provider $provider): void
    {
        $class = get_class($provider);

        if (defined($class.'::AUTO_REGISTER') && $class::AUTO_REGISTER) {
            $provider->registerServices($this);
            return;
        }

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
    public function bind(string $type, $target=null): Binding
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
    public function bindOnce(string $type, $target=null, callable $callback=null): Binding
    {
        if (isset($this->bindings[$type])) {
            return new Binding($this, $type, $target);
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
    public function bindToGroup(string $type, $target=null): Binding
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
    public function bindShared(string $type, $target=null): Binding
    {
        return $this->bind($type, $target)->setShared(true);
    }

    /**
     * Bind singleton only if it's not bound already
     */
    public function bindSharedOnce(string $type, $target=null, callable $callback=null): Binding
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
    public function bindSharedToGroup(string $type, $target=null): Binding
    {
        return $this->bindToGroup($type, $target)->setShared(true);
    }


    /**
     * Set an alias for an existing binding
     */
    public function alias(string $type, string $alias): Container
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
            return (string)$key;
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
    public function each(string $type, callable $callback): Container
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
    public function remove(string $type): Container
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
    public function getBinding(string $type): Binding
    {
        if ($binding = $this->lookupBinding($type)) {
            return $binding;
        }

        throw Exceptional::{
            'NotFound,Psr\\Container\\NotFoundExceptionInterface'
        }(
            $type.' has not been bound'
        );
    }

    /**
     * Look up binding without throwing an error
     */
    protected function lookupBinding(string $type): ?Binding
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

        return null;
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
    public function prepareWith(string $type, callable $callback): Container
    {
        $this->getBinding($type)->prepareWith($callback);
        return $this;
    }

    /**
     * Add a single injection parameter
     */
    public function inject(string $type, string $name, $value): Container
    {
        $this->getBinding($type)->inject($name, $value);
        return $this;
    }

    /**
     * Add an array of injection parameters
     */
    public function addParams(string $type, array $params): Container
    {
        $this->getBinding($type)->addParams($params);
        return $this;
    }

    /**
     * Clear injected params from binding
     */
    public function clearParams(string $type): Container
    {
        $this->getBinding($type)->clearParams();
        return $this;
    }

    /**
     * Clear injected params from all bindings
     */
    public function clearAllParams(): Container
    {
        foreach ($this->bindings as $binding) {
            $binding->clearParams();
        }

        return $this;
    }



    /**
     * Reset everything
     */
    public function clear(): Container
    {
        $this->bindings = [];
        $this->aliases = [];

        if ($this->events) {
            $this->events->clear();
        }

        return $this;
    }



    /**
     * Create a new instanceof $type
     */
    public function newInstanceOf(string $type, array $params=[], string ...$interfaces): object
    {
        if (!$binding = $this->lookupBinding($type)) {
            $binding = new Binding($this, $type, $type, false);
        }

        $binding->addParams($params);
        $output = $binding->getInstance();
        return $this->testInterfaces($output, ...$interfaces);
    }

    /**
     * Create new instance of type, no looking up bindinh
     */
    public function buildInstanceOf(string $type, array $params=[], string ...$interfaces): object
    {
        $reflector = new \ReflectionClass($type);

        if (!$reflector->isInstantiable()) {
            throw Exceptional::{
                'Logic,Psr\\Container\\ContainerExceptionInterface'
            }(
                'Binding target '.$type.' cannot be instantiated'
            );
        }

        if (!$constructor = $reflector->getConstructor()) {
            return $reflector->newInstance();
        }

        $paramReflectors = $constructor->getParameters();
        $args = $this->prepareArgs($paramReflectors, $params);

        $output = $reflector->newInstanceArgs($args);
        return $this->testInterfaces($output, ...$interfaces);
    }

    /**
     * Test object for interfaces
     */
    protected function testInterfaces(object $object, string ...$interfaces): object
    {
        foreach ($interfaces as $interface) {
            if (!$object instanceof $interface) {
                throw Exceptional::Implementation(
                    'Binding target does not implement '.$interface
                );
            }
        }

        return $object;
    }

    /**
     * Call any function with injected params
     */
    public function call(callable $function, array $params=[])
    {
        if (is_array($function)) {
            $classRef = new \ReflectionObject($function[0]);
            $reflector = $classRef->getMethod($function[1]);
        } elseif ($function instanceof \Closure || is_string($function)) {
            $reflector = new \ReflectionFunction($function);
        } else {
            throw Exceptional::InvalidArgument(
                'Unable to reflect callback', null, $function
            );
        }

        $paramReflectors = $reflector->getParameters();
        $args = $this->prepareArgs($paramReflectors, $params);

        return call_user_func_array($function, $args);
    }

    /**
     * Get params for function
     */
    protected function prepareArgs(array $paramReflectors, array $params): array
    {
        $args = [];

        foreach ($paramReflectors as $i => $reflector) {
            if (array_key_exists($reflector->name, $params)) {
                $args[] = $params[$reflector->name];
                continue;
            }

            if (null !== ($class = $reflector->getClass())) {
                try {
                    $args[] = $this->get($class->name);
                } catch (NotFoundExceptionInterface $e) {
                    if ($reflector->isOptional()) {
                        $args[] = $reflector->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            } elseif ($reflector->isDefaultValueAvailable()) {
                $args[] = $reflector->getDefaultValue();
            } elseif ($i === 0 && $reflector->name === 'app') {
                $args[] = $this;
            } else {
                throw Exceptional::{
                    'Logic,Psr\\Container\\ContainerExceptionInterface'
                }(
                    'Binding param $'.$reflector->name.' cannot be resolved'
                );
            }
        }

        return $args;
    }



    /**
     * Force a binding to forget its shared instance
     */
    public function forgetInstance(string $type): Binding
    {
        $binding = $this->getBinding($type);
        $binding->forgetInstance();
        return $binding;
    }

    /**
     * Force all bindings to forget shared instances
     */
    public function forgetAllInstances(): Container
    {
        foreach ($this->bindings as $binding) {
            $binding->forgetInstance();
        }

        return $this;
    }




    /**
     * Add an event handler for when instances are created
     */
    public function afterResolving(string $type, callable $callback): Container
    {
        $this->events()->after('resolving.'.$type, $callback);
        return $this;
    }

    /**
     * Trigger events on building a new instance
     */
    public function triggerAfterResolving(Binding $binding, object $instance)
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
    public function afterRebinding(string $type, callable $callback): Container
    {
        $this->events()->after('rebinding.'.$type, $callback);
        return $this;
    }

    /**
     * Trigger rebinding events
     */
    public function triggerAfterRebinding(Binding $binding): void
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
    public function __get(string $type): Binding
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
    public function __unset(string $type): void
    {
        $this->remove($type);
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
    public function offsetSet($type, $target): void
    {
        $this->bind($type, $target);
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
    public function offsetUnset($type): void
    {
        $this->remove($type);
    }


    /**
     * Prepare event dispatcher
     */
    protected function events(): Dispatcher
    {
        if (!$this->events) {
            $this->events = new Dispatcher();
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

        foreach ($this->providers as $type => $provider) {
            $alias = Binding::typeToAlias($type) ?? $type;
            $output[$alias] = 'provider : '.get_class($provider);
        }

        return $output;
    }
}
