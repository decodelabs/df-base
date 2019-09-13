<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Crypt\Hasher;

use Df;
use Df\Core\Crypt\IHasher;
use Df\Core\Crypt\Hasher;

class Native implements IHasher
{
    protected $algo;
    protected $options = [];

    /**
     * Init with algo and options
     */
    public function __construct(int $algo=null, array $options=[])
    {
        if ($algo === null) {
            $algo = PASSWORD_DEFAULT;
        }

        $this->algo = $algo;
        $this->options = $options;
    }

    /**
     * Get details about hashed value
     */
    public function getInfo(string $hashedValue): array
    {
        return password_get_info($hashedValue);
    }

    /**
     * One-way hash of string using currently active algo
     */
    public function hash(string $value): string
    {
        $output = password_hash($value, $this->algo, $this->options);

        if ($output === false) {
            throw Glitch::ERuntime(
                'Hashing failed with current options'
            );
        }

        return $output;
    }

    /**
     * Check original value against hashed value
     */
    public function verify(string $value, string $hashedValue): bool
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Check to see if a hashed value is outdated and needs regenerating
     */
    public function needsRehash(string $hashedValue): bool
    {
        return password_needs_rehash($hashedValue, $this->algo, $this->options);
    }
}
