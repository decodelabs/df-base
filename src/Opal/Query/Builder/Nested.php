<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Nestable;

interface Nested extends Builder
{
    public function withKey(string ...$fields): Nested;
    public function getKeyFields(): ?array;
    public function clearKeyFields(): Nested;

    public function setName(string $name): Nested;
    public function getName(): string;

    public function setCopy(bool $copy): Nested;
    public function isCopy(): bool;

    public function as(string $name): Nestable;
    public function asCopy(string $name): Nestable;
    public function endNest(string $name): Nestable;
}
