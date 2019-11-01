<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Config;

use Df\Core\App;

interface Loader
{
    public function loadConfig(App $app): Repository;
}
