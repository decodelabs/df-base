<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\IField;

interface INested extends IBuilder
{
    public function withKey(string ...$fields): INested;
    public function getKeyFields(): ?array;
    public function clearKeyFields(): INested;

    public function setName(string $name): INested;
    public function getName(): string;

    public function setCopy(bool $copy): INested;
    public function isCopy(): bool;

    public function as(string $name): INestable;
    public function asCopy(string $name): INestable;
    public function endNest(string $name): INestable;
}
