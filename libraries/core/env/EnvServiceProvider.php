<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\env;

use df;
use df\core;
use df\core\service\IContainer;
use df\core\service\IProvider;

class EnvServiceProvider implements IProvider
{
    public static function getProvidedServices(): array
    {
        return [
            core\env\IConfig::class
        ];
    }

    public function registerServices(IContainer $app): void
    {
        $app->bindShared(core\env\IConfig::class, function ($app) {
            return core\env\config\DotIni::loadFile(df\BASE_PATH.'/.env');
        });
    }
}
