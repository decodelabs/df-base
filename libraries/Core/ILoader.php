<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core;

use Df;

interface ILoader
{
    public function loadPackages(array $packages): void;

    public function getBasePath(): string;
    public function getVendorPath(): string;

    public function getApexPaths(): array;
    public function getLibraryPaths(): array;
}
