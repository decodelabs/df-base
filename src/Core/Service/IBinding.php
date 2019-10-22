<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Service;

interface IBinding
{
    public function getContainer(): IContainer;
    public function getType(): string;

    public function setTarget($target): IBinding;
    public function getTarget();

    public function setFactory(\Closure $factory);
    public function getFactory(): ?\Closure;

    public function alias(string $alias): IBinding;
    public function getAlias(): ?string;
    public function hasAlias(): bool;
    public function removeAlias(): IBinding;

    public function isShared(): bool;
    public function setShared(bool $shared): IBinding;

    public function prepareWith(callable $callback): IBinding;
    public function hasPreparators(): bool;
    public function clearPreparators(): IBinding;

    public function inject(string $name, $value): IBinding;
    public function getParam(string $name);
    public function addParams(array $params): IBinding;
    public function hasParam(string $name): bool;
    public function removeParam(string $name): IBinding;
    public function clearParams(): IBinding;

    public function setInstance(object $instance): IBinding;
    public function forgetInstance(): IBinding;
    public function getInstance(): object;
    public function hasInstance(): bool;
    public function newInstance(): object;
    public function getGroupInstances(): array;
    public function describeInstance();

    public function afterResolving(callable $callback): IBinding;
    public function afterRebinding(callable $callback): IBinding;
}
