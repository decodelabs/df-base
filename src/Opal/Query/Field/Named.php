<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Field;

interface Named extends Field
{
    public function getName(): string;
}
