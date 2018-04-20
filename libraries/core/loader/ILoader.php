<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\loader;

use df;

interface ILoader
{
    public function loadPackages(array $packages): void;
    public function getApexPaths(): array;
    public function getLibraryPaths(): array;
}
