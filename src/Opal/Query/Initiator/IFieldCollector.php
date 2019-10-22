<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Initiator;

use Df\Opal\Query\IInitiator;

interface IFieldCollector extends IInitiator
{
    public function setFields(string ...$fields): IFieldCollector;
    public function addFields(string ...$fields): IFieldCollector;
    public function getFields(): array;
    public function clearFields(): IFieldCollector;
}
