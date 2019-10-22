<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;

interface IFieldCollector extends IBuilder
{
    public function setFields(string ...$fields): IFieldCollector;
    public function addFields(string ...$fields): IFieldCollector;
    public function getFields(): array;
    public function clearFields(): IFieldCollector;
}
