<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\core\env;

use df;

interface IValidator
{
    public function getName(): string;
    public function asString(string $default=null): ?string;
    public function asBool(bool $default=null): ?bool;
    public function asInt(int $default=null): ?int;
    public function asFloat(float $default=null): ?float;

    public function isEmpty(): bool;
    public function isBool(): bool;
    public function isInt(): bool;
    public function isFloat(): bool;
    public function isIn(...$values): bool;

    public function checkEmpty(): IValidator;
    public function checkBool(): IValidator;
    public function checkInt(): IValidator;
    public function checkFloat(): IValidator;
    public function checkIn(...$values): IValidator;
}
