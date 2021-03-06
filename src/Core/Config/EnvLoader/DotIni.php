<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config\EnvLoader;

use Df;
use Df\Core\App;
use Df\Core\Config\EnvLoader;
use Df\Core\Config\Env;

use DecodeLabs\Exceptional;

class DotIni implements EnvLoader
{
    protected $path;

    /**
     * Init with optional path
     */
    public function __construct(string $path=null)
    {
        if (!empty($path)) {
            $this->path = $path;
        }
    }


    /**
     * Set load path
     */
    public function setPath(?string $path): EnvLoader
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get load path
     */
    public function getPath(): string
    {
        return $this->path ?? Df\BASE_PATH.'/.env';
    }


    /**
     * Load ini file from app root
     */
    public function loadEnvConfig(App $app): Env
    {
        $path = $this->getPath();

        if (!is_readable($path) || !is_file($path)) {
            throw Exceptional::NotFound(
                'Ini file could not be read', null, $path
            );
        }

        if (false === ($data = parse_ini_file($path))) {
            throw Exceptional::Runtime(
                'Unable to parse ini file', null, $path
            );
        }

        if (!isset($data['IDENTITY'])) {
            throw Exceptional::UnexpectedValue(
                'Env data does not define an IDENTITY'
            );
        }

        $identity = $data['IDENTITY'];
        unset($data['IDENTITY']);

        return new Env($identity, $data);
    }
}
