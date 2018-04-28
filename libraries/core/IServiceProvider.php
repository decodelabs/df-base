<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core;

use df;
use df\core;

interface IServiceProvider
{
    public static function getProvidedServices(): array;
    public function registerServices(core\IContainer $container): void;
}
