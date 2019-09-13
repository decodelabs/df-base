<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Config\EnvLoader;

use Df;

use Df\Core\IApp;

use Df\Core\Config\IEnvLoader;
use Df\Core\Config\Env;

class DotIni implements IEnvLoader
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
    public function setPath(?string $path): IEnvLoader
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get load path
     */
    public function getPath(): string
    {
        return $this->path ?? $app->getBasePath().'/.env';
    }


    /**
     * Load ini file from app root
     */
    public function loadEnvConfig(IApp $app): Env
    {
        $path = $this->getPath();

        if (!is_readable($path) || !is_file($path)) {
            throw Glitch::ENotFound('Ini file could not be read', null, $path);
        }

        $data = parse_ini_file($path);

        if (!isset($data['IDENTITY'])) {
            throw Glitch::EUnexpectedValue(
                'Env data does not define an IDENTITY'
            );
        }

        $identity = $data['IDENTITY'];
        unset($data['IDENTITY']);

        return new Env($identity, $data);
    }
}
