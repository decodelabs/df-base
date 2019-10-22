<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core;

interface ILoader
{
    public function loadBundles(array $bundles): void;
    public function getLoadedBundles(): array;

    public function getBasePath(): string;
    public function getVendorPath(): string;
}
