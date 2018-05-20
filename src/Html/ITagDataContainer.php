<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Html;

use Df;
use Df\Data\IAttributeContainer;

interface ITagDataContainer extends IContainer, IAttributeContainer
{
    // Class attributes
    public function setClasses(string ...$classes): ITagDataContainer;
    public function addClasses(string ...$classes): ITagDataContainer;
    public function getClasses(): ClassList;
    public function setClass(string ...$classes): ITagDataContainer;
    public function addClass(string ...$classes): ITagDataContainer;
    public function removeClass(string ...$classes): ITagDataContainer;
    public function hasClass(string ...$classes): bool;
    public function hasClasses(string ...$classes): bool;
    public function countClasses(): int;

    // Direct attributes
    public function setId(?string $id): ITagDataContainer;
    public function getId(): ?string;

    public function setHidden(bool $hidden): ITagDataContainer;
    public function isHidden(): bool;
    public function hide(): ITagDataContainer;
    public function show(): ITagDataContainer;

    public function setTitle(?string $title): ITagDataContainer;
    public function getTitle(): ?string;


    // Style
    public function setStyles(...$styles): ITagDataContainer;
    public function addStyles(...$styles): ITagDataContainer;
    public function getStyles(): StyleList;
    public function setStyle(string $key, ?string $value): ITagDataContainer;
    public function getStyle(string $key): ?string;
    public function removeStyle(string ...$keys): ITagDataContainer;
    public function hasStyle(string ...$keys): bool;
    public function hasStyles(string ...$keys): bool;
}
