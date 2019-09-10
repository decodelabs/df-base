<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use DecodeLabs\Collections\Tree\NativeMutable as MutableTree;

class Repository extends MutableTree
{
    const KEY_SEPARATOR = '.';
}
