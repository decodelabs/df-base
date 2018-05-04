<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config;

use Df;

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
            IRepository::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        $app->bindShared(IRepository::class, function ($app) {
            // TODO: load this from loader
            $config = [
                'arch' => [
                    'areaMaps' => [
                        '*' => 'df.test:8080/test/df-playground-/',
                        'admin' => 'df.test:8080/test/df-playground-/admin/',
                        'shared' => 'df.test:8080/test/df-playground-/~{name-test}/{stuff}',
                        'devtools' => 'devtools.df.test:8080/test/df-playground-/'
                    ]
                ]
            ];

            return new Repository($config);
        });
    }
}
