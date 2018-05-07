<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Crypt;

use Df;

interface IHasher
{
    public function getInfo(string $hashedValue): array;
    public function hash(string $value): string;
    public function verify(string $value, string $hashedValue): bool;
    public function needsRehash(string $hashedValue): bool;
}
