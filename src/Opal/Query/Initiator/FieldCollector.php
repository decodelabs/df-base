<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Initiator;

use Df\Opal\Query\Initiator;

interface FieldCollector extends Initiator
{
    public function setFields(string ...$fields): FieldCollector;
    public function addFields(string ...$fields): FieldCollector;
    public function getFields(): array;
    public function clearFields(): FieldCollector;
}
