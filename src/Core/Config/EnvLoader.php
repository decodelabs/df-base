<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Config;

use Df\Core\App;

interface EnvLoader
{
    public function loadEnvConfig(App $app): Env;
}
