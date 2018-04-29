<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core;

use df;
use df\core\service\IContainer;

interface IApp extends IContainer
{
    public function bootstrap(): void;

    public function getBasePath(): string;
    public function getVendorPath(): string;
    public function getPublicPath(): string;
}
