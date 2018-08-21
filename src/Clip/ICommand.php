<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

use Df\Clip\IShell;
use Df\Clip\Command\IArgument;
use Df\Clip\Command\IRequest;

interface ICommand
{
    public function setPath(string $path): ICommand;
    public function getPath(): string;

    public function setHelp(?string $help): ICommand;
    public function getHelp(): ?string;

    public function addArgument(string $name, string $description, callable $setup=null): ICommand;
    public function setArgument(IArgument $arg): ICommand;
    public function getArgument(string $name): ?IArgument;
    public function getArguments(): array;
    public function removeArgument(string $name): ICommand;
    public function clearArguments(): ICommand;

    public function apply(IRequest $request): array;
    public function renderHelp(IShell $shell): void;
}
