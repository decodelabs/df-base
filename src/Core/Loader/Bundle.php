<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Loader;

use Df;

class Bundle
{
    protected static $bundles = [];

    protected $name;
    protected $priority = 20;
    protected $paths = [];

    /**
     * Add meta info for bundle
     */
    public static function register(string $name, int $priority, array $paths): void
    {
        self::$bundles[$name] = new self($name, $priority, $paths);
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
