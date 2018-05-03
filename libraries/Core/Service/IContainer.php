<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Service;

use Df;

use Psr\Container\ContainerInterface;

interface IContainer extends ContainerInterface, \ArrayAccess
{
    public function registerProviders(string ...$providers): void;
    public function registerProvider(string $provider): void;
    public function registerProviderInstance(IProvider $provider): void;

    public function getProviders(): array;

    public function bind(string $type, $target=null): IBinding;
    public function bindOnce(string $type, $target=null, callable $callback=null): IBinding;
    public function bindToGroup(string $type, $target=null): IBinding;
    public function bindShared(string $type, $target=null): IBinding;
    public function bindSharedOnce(string $type, $target=null, callable $callback=null): IBinding;
    public function bindSharedToGroup(string $type, $target=null): IBinding;

    public function alias(string $type, string $alias): IContainer;
    public function getAlias(string $type): ?string;
    public function hasAlias(string $alias): bool;
    public function isAliased(string $type): bool;
    public function getAliasedType(string $alias): ?string;

    public function registerAlias(string $type, string $alias): void;
    public function unregisterAlias(string $alias): void;

    //public function get(string $type);
    public function getWith(string $type, array $params=[]): object;
    public function getGroup(string $type): array;
    public function each(string $type, callable $callback): IContainer;

    //public function has(string $type): bool;
    public function remove(string $type): IContainer;

    public function getBinding(string $type): IBinding;
    public function getBindings(): array;

    public function prepareWith(string $type, callable $callback): IContainer;
    public function inject(string $type, string $name, $value): IContainer;
    public function addParams(string $type, array $params): IContainer;
    public function clearParams(string $type): IContainer;
    public function clearAllParams(): IContainer;

    public function clear(): IContainer;

    public function newInstanceOf(string $type, array $params=[]): object;

    public function forgetInstance(string $type): IBinding;
    public function forgetAllInstances(): IContainer;

    public function afterResolving(string $type, callable $callback): IContainer;
    public function triggerAfterResolving(IBinding $binding, object $instancegetWith);
    public function afterRebinding(string $type, callable $callback): IContainer;
    public function triggerAfterRebinding(IBinding $newBinding): void;

    public function __get(string $type): IBinding; // getBinding()
    public function __set(string $type, $target); // bind()
    public function __isset(string $type): bool; // has()
    public function __unset(string $type); // remove()
}
