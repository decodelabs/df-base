<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Html;

use Df;

interface ITag extends ITagDataContainer, \ArrayAccess
{
    // Name
    public function setName(string $name): ITag;
    public function getName(): string;

    public function isInline(): bool;
    public function isBlock(): bool;

    // Strings
    public function open(): string;
    public function close(): string;

    public function renderWith(IContainer $content=null): ?IContainer;
    public function renderWithRaw(string $content=null): ?IContainer;

    public function setRenderEmpty(bool $render): ITag;
    public function willRenderEmpty(): bool;
}
