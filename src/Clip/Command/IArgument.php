<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip\Command;

use Df;

interface IArgument
{
    public function getName(): string;

    public function setDescription(string $description): IArgument;
    public function getDescription(): string;

    public function setNamed(bool $named): IArgument;
    public function isNamed(): bool;

    public function setShortcut(?string $shortcut): IArgument;
    public function getShortcut(): ?string;

    public function setBoolean(bool $boolean): IArgument;
    public function isBoolean(): bool;

    public function setOptional(bool $optional): IArgument;
    public function isOptional(): bool;

    public function setList(bool $list): IArgument;
    public function isList(): bool;

    public function setDefaultValue(?string $value): IArgument;
    public function getDefaultValue(): ?string;

    public function setPattern(?string $pattern): IArgument;
    public function getPattern(): ?string;


    public function validate($value);
}
