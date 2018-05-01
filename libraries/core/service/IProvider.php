<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\service;

use df;
use df\core;

interface IProvider
{
    public static function getProvidedServices(): array;
    public function registerServices(IContainer $container): void;
}
