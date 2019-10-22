<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Loader;

use DecodeLabs\Glitch;

class Bundle
{
    protected static $bundles = [];

    protected $name;
    protected $priority = 20;
    protected $paths = [];

    /**
     * Add meta info for bundle
     */
    public static function register(string $name, int $priority, string $pathBase, array $paths): void
    {
        $pathBase = rtrim($pathBase, '/');
        Glitch::registerPathAlias($name, $pathBase);

        self::$bundles[$name] = new self($name, $priority, array_map(function ($path) use ($pathBase) {
            return $pathBase.'/'.ltrim($path, '/');
        }, $paths));
    }

    /**
     * Get full list of bundles
     */
    public static function getRegistered(): array
    {
        return self::$bundles;
    }

    /**
     * Protected init with registration details
     */
    protected function __construct(string $name, int $priority, array $paths)
    {
        $this->name = $name;
        $this->priority = $priority;
        $this->paths = $paths;
    }

    /**
     * Get bundle name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get paths for loader
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
