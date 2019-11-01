<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Service;

use Df\Core\Service\Container;

interface Provider
{
    public static function getProvidedServices(): array;
    public function registerServices(Container $container): void;
}
