<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Config;

use Df\Core\IApp;

interface IEnvLoader
{
    public function loadEnvConfig(IApp $app): Env;
}
