<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Env;

use Df;

interface IConfig extends \ArrayAccess
{
    public function get(string $key, string $default=null): ?string;
    public function getBool(string $key, bool $default=null): ?bool;
    public function getInt(string $key, int $default=null): ?int;
    public function getFloat(string $key, float $default=null): ?float;
    public function getMap(string ...$keys): array;
    public function getAll(): array;

    public function set(string $key, string $value): IConfig;
    public function setMap(array $map): IConfig;
    public function remove(string ...$keys): IConfig;

    public function has(string ...$keys): bool;
    public function checkExists(string ...$keys): IConfig;
    public function __get(string $key): IValidator;
}
