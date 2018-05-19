<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Data;

use Df;

interface IAttributeContainer
{
    public function setAttributes(array $attributes): IAttributeContainer;
    public function addAttributes(array $attributes): IAttributeContainer;
    public function getAttributes(): array;
    public function setAttribute(string $key, $value): IAttributeContainer;
    public function getAttribute(string $key);
    public function removeAttribute(string ...$keys): IAttributeContainer;
    public function hasAttribute(string ...$keys): bool;
    public function clearAttributes(): IAttributeContainer;
    public function countAttributes(): int;
}
