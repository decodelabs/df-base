<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core;

use Df\Core\Service\Container;

interface App extends Container
{
    public function bootstrap(): void;

    public function getGlobalMiddleware(): array;

    public function getBasePath(): string;
    public function getVendorPath(): string;
    public function getPublicPath(): string;
    public function getStoragePath(): string;

    public function getAppName(): string;

    public function terminate(): void;
}
