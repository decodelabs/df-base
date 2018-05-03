<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Env;

use Df;

use Df\Core\Env\IConfig;
use Df\Core\Service\IContainer;
use Df\Core\Service\IProvider;

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
            return namespace\Config\DotIni::loadFile(df\BASE_PATH.'/.env');
        });
    }
}
