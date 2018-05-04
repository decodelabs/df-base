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
            IEnv::class,
            IRepository::class
        ];
    }

    /**
     * Load provided classes into app
     */
    public function registerServices(IContainer $app): void
    {
        // Env
        $app->bindShared(IEnv::class, function ($app) {
            $path = $app->getBasePath().'/.env';

            if (!is_readable($path) || !is_file($path)) {
                throw Df\Error::{'ENotFound'}('Ini file could not be read', null, $path);
            }

            $data = parse_ini_file($path);

            if (!isset($data['IDENTITY'])) {
                throw Df\Error::EUnexpectedValue(
                    'Env data does not define an IDENTITY'
                );
            }

            $identity = $data['IDENTITY'];
            unset($data['IDENTITY']);

            return new Env($identity, $data);
        });



        // Config
        $app->bindShared(IRepository::class, function ($app, IEnv $env) {
            // TODO: load this from loader
            $config = [
                'arch' => [
                    'areaMaps' => [
                        '*' => 'df.test:8080/test/df-playground-/',
                        'admin' => 'df.test:8080/test/df-playground-/admin/',
                        'shared' => 'df.test:8080/test/df-playground-/~{name-test}/{stuff}',
                        'devtools' => 'devtools.df.test:8080/test/df-playground-/'
                    ]
                ],
                'http' => [
                    'sendfile' => 'x-sendfile',
                    'manualChunk' => true
                ]
            ];

            return new Repository($config);
        });
    }
}
