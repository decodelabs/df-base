<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Cache;

use Df\Core\Config\Repository;
use Df\Core\Cache\Driver;
use Df\Core\Cache\Store;
use Df\Core\Cache\Store\Generic as GenericStore;

use DecodeLabs\Glitch;

class Manager
{
    protected $caches = [];
    protected $config;

    /**
     * Init with config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Get cache pool
     */
    public function get(string $namespace): Store
    {
        if (isset($this->caches[$namespace])) {
            return $this->caches[$namespace];
        }

        $driver = $this->getDriverFor($namespace, $conf);
        $store = new GenericStore($namespace, $driver);

        if ($conf instanceof Repository) {
            if (isset($conf->pileUpPolicy)) {
                $store->setPileUpPolicy($conf['pileUpPolicy']);
            }

            if (isset($conf->preemptTime)) {
                $store->setPreemptTime((int)$conf['preemptTime']);
            }

            if (isset($conf->sleepTime)) {
                $store->setSleepTime((int)$conf['sleepTime']);
            }

            if (isset($conf->sleepAttempts)) {
                $store->setSleepAttempts((int)$conf['sleepAttempts']);
            }
        }

        return $this->caches[$namespace] = $store;
    }

    /**
     * Get a driver for a specific namespace from config
     */
    public function getDriverFor(string $namespace, Repository &$config=null): Driver
    {
        foreach ([$namespace, 'default'] as $name) {
            if (!isset($this->config->stores->{$name}->driver)) {
                continue;
            }

            $config = clone $this->config->stores->{$name};

            if (!$config instanceof Repository) {
                throw Glitch::EUnexpectedValue('Config is not a Repository', null, $config);
            }

            try {
                if ($driver = $this->loadDriver($config['driver'], $config)) {
                    return $driver;
                }
            } catch (\Throwable $e) {
                Glitch::logException($e);
            }
        }

        $config = null;

        foreach (['Memcache', 'Redis', 'Apcu', 'Predis', 'PhpFile'] as $name) {
            try {
                if ($driver = $this->loadDriver($name, $config)) {
                    return $driver;
                }
            } catch (\Throwable $e) {
                Glitch::logException($e);
            }
        }

        return new Driver\PhpArray();
    }

    /**
     * Load driver by name
     */
    public function loadDriver(string $name, Repository &$directConf=null): ?Driver
    {
        $class = 'Df\\Core\\Cache\\Driver\\'.$name;

        if (!class_exists($class)) {
            throw Glitch::{'EInvalidArgument,ENotFound'}(
                'Cache driver '.$name.' could not be found'
            );
        }

        if (!$class::isAvailable()) {
            return null;
        }

        $conf = clone $this->config->drivers->{$name};

        if ($directConf) {
            $directConf = $conf->merge($directConf);
        }

        if ($conf['enabled'] === false) {
            return null;
        }

        return $class::fromConfig($conf);
    }

    /**
     * Purge all drivers
     */
    public function purgeAll(): void
    {
        $names = [];

        foreach ($this->config->stores as $name => $conf) {
            if (!isset($conf->driver)) {
                continue;
            }

            if (!$conf instanceof Repository) {
                throw Glitch::EUnexpectedValue('Config is not a Repository', null, $conf);
            }

            try {
                if (!$driver = $this->loadDriver($name = $conf['driver'], $conf)) {
                    continue;
                }
            } catch (\Throwable $e) {
                Glitch::logException($e);
                continue;
            }

            $names[] = $name;
            $driver->purge();
        }

        foreach (['Memcache', 'Redis', 'Apcu', 'Predis', 'PhpFile', 'File'] as $name) {
            if (in_array($name, $names)) {
                continue;
            }

            try {
                if (!$driver = $this->loadDriver($name)) {
                    continue;
                }
            } catch (\Throwable $e) {
                Glitch::logException($e);
                continue;
            }

            $driver->purge();
        }
    }
}
