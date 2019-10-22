<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Field;

use Df\Opal\Query\IField;

interface INamed extends IField
{
    public function getName(): string;
}
