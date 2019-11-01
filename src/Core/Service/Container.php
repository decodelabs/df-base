<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Service;

use Df\Core\Service\Provider;
use Df\Core\Service\Binding;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface, \ArrayAccess
{
    public function registerProviders(string ...$providers): void;
    public function registerProvider(string $provider): void;
    public function registerProviderInstance(Provider $provider): void;

    public function getProviders(): array;

    public function bind(string $type, $target=null): Binding;
    public function bindOnce(string $type, $target=null, callable $callback=null): Binding;
    public function bindToGroup(string $type, $target=null): Binding;
    public function bindShared(string $type, $target=null): Binding;
    public function bindSharedOnce(string $type, $target=null, callable $callback=null): Binding;
    public function bindSharedToGroup(string $type, $target=null): Binding;

    public function alias(string $type, string $alias): Container;
    public function getAlias(string $type): ?string;
    public function hasAlias(string $alias): bool;
    public function isAliased(string $type): bool;
    public function getAliasedType(string $alias): ?string;

    public function registerAlias(string $type, string $alias): void;
    public function unregisterAlias(string $alias): void;

    //public function get(string $type);
    public function getWith(string $type, array $params=[]): object;
    public function getGroup(string $type): array;
    public function each(string $type, callable $callback): Container;

    //public function has(string $type): bool;
    public function remove(string $type): Container;

    public function getBinding(string $type): Binding;
    public function getBindings(): array;

    public function prepareWith(string $type, callable $callback): Container;
    public function inject(string $type, string $name, $value): Container;
    public function addParams(string $type, array $params): Container;
    public function clearParams(string $type): Container;
    public function clearAllParams(): Container;

    public function clear(): Container;

    public function newInstanceOf(string $type, array $params=[], string ...$interfaces): object;
    public function buildInstanceOf(string $type, array $params=[], string ...$interfaces): object;
    public function call(callable $function, array $params=[]);

    public function forgetInstance(string $type): Binding;
    public function forgetAllInstances(): Container;

    public function afterResolving(string $type, callable $callback): Container;
    public function triggerAfterResolving(Binding $binding, object $instancegetWith);
    public function afterRebinding(string $type, callable $callback): Container;
    public function triggerAfterRebinding(Binding $newBinding): void;

    public function __get(string $type): Binding; // getBinding()
    public function __set(string $type, $target); // bind()
    public function __isset(string $type): bool; // has()
    public function __unset(string $type); // remove()
}
