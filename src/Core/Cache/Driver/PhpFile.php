<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache\Driver;

use Df;
use Df\Core\Fs\IFile;

class PhpFile extends File
{
    const EXTENSION = '.php';

    /**
     * Store item data in file
     */
    protected function buildFileContent(IFile $file, string $namespace, string $key, $value, int $created, ?int $expires): string
    {
        $output = '<?php'.PHP_EOL.'return ';

        $output .= var_export([
            'namespace' => $namespace,
            'key' => $key,
            'expires' => $expires,
            'value' => $value
        ], true).';';

        return $output;
    }

    /**
     * Get item data from file
     */
    protected function loadFileContent(IFile $file): ?array
    {
        try {
            $data = require (string)$file;
        } catch (\Throwable $e) {
            return null;
        }

        if (is_null($data) || !is_array($data)) {
            return null;
        }

        return $data;
    }
}
