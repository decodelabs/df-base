<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use Df;
use Df\Data\Tree;

class Repository extends Tree implements IRepository
{
    const KEY_SEPARATOR = '.';
}
