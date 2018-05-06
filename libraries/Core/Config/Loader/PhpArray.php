<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config\Loader;

use Df;

use Df\Core\IApp;

use Df\Core\Config\Repository;
use Df\Core\Config\ILoader;

class PhpArray implements ILoader
{
    const CONFIG = [
        'arch' => [
            'areaMaps' => [
                '*' => 'http://df.test:8080/test/df-playground-/',
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

    /**
     * Load config repository from php array files in app folder
     */
    public function loadConfig(IApp $app): Repository
    {
        return new Repository(static::CONFIG);
    }
}
