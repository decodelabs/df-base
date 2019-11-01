<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config\Loader;

use Df\Core\App;

use Df\Core\Config\Repository;
use Df\Core\Config\Loader;

class PhpArray implements Loader
{
    const CONFIG = [
        'arch' => [
            'areaMaps' => [
                '*' => 'df.test/test/df-playground-/'
            ]
        ],
        'crypt' => [
            'algo' => PASSWORD_DEFAULT
        ],
        'http' => [
            'sendfile' => 'x-sendfile',
            'manualChunk' => true
        ],
        'cache' => [
            'stores' => [
                'default' => [
                    'driver' => 'Apcu'
                ]
            ]
        ]
    ];

    /**
     * Load config repository from php array files in app folder
     */
    public function loadConfig(App $app): Repository
    {
        return new Repository(static::CONFIG);
    }
}
