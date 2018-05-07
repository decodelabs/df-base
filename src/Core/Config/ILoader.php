<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Config;

use Df;
use Df\Core\IApp;

interface ILoader
{
    public function loadConfig(IApp $app): Repository;
}
