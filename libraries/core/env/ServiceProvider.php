<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\env;

use df;

use df\core\env\IConfig;
use df\core\service\IContainer;
use df\core\service\IProvider;

class ServiceProvider implements IProvider
{
    /**
     * Get list of provided classes
     */
    public static function getProvidedServices(): array
    {
        return [
            IConfig::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        $app->bindShared(IConfig::class, function ($app) {
            return namespace\config\DotIni::loadFile(df\BASE_PATH.'/.env');
        });
    }
}
