<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Config;

use Df;

interface IEnv extends \ArrayAccess
{
    public function getIdentity(): string;

    public function get(string $key, string $default=null): ?string;
    public function getBool(string $key, bool $default=null): ?bool;
    public function getInt(string $key, int $default=null): ?int;
    public function getFloat(string $key, float $default=null): ?float;
    public function getMap(string ...$keys): array;
    public function getAll(): array;

    public function set(string $key, string $value): IEnv;
    public function setMap(array $map): IEnv;
    public function remove(string ...$keys): IEnv;

    public function has(string ...$keys): bool;
    public function checkExists(string ...$keys): IEnv;
}
