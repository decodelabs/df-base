<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Service;

interface IProvider
{
    public static function getProvidedServices(): array;
    public function registerServices(IContainer $container): void;
}
