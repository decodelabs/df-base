<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Flex;

use Df\Time\Date;

use Ramsey\Uuid\Uuid as UuidLib;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;

use DecodeLabs\Glitch;

class Uuid
{
    const DNS = UuidLib::NAMESPACE_DNS;
    const URL = UuidLib::NAMESPACE_URL;
    const OID = UuidLib::NAMESPACE_OID;
    const X500 = UuidLib::NAMESPACE_X500;

    /**
     * Generate a Uuid1 string
     */
    public static function v1String($node=null, int $clockSeq=null): string
    {
        return (string)UuidLib::uuid1($node, $clockSeq);
    }

    /**
     * Generate a Uuid1 object
     */
    public static function v1($node=null, int $clockSeq=null): UuidInterface
    {
        return UuidLib::uuid1($node, $clockSeq);
    }

    /**
     * Generate a Uuid3 string
     */
    public static function v3String(string $ns, string $name): string
    {
        return (string)UuidLib::uuid3($node, $clockSeq);
    }

    /**
     * Generate a Uuid3 object
     */
    public static function v3(string $ns, string $name): UuidInterface
    {
        return UuidLib::uuid3($ns, $name);
    }

    /**
     * Generate a Uuid4 string
     */
    public static function v4String(): string
    {
        return (string)UuidLib::uuid4();
    }

    /**
     * Generate a Uuid4 object
     */
    public static function v4(): UuidInterface
    {
        return UuidLib::uuid4();
    }

    /**
     * Generate a COMB string
     */
    public static function combString(): string
    {
        return (string)self::comb();
    }

    /**
     * Generate a COMB object
     */
    public static function comb(): UuidInterface
    {
        static $factory;

        if ($factory === null) {
            $factory = new \Ramsey\Uuid\UuidFactory();
            $generator = new \Ramsey\Uuid\Generator\CombGenerator($factory->getRandomGenerator(), $factory->getNumberConverter());
            $codec = new \Ramsey\Uuid\Codec\TimestampFirstCombCodec($factory->getUuidBuilder());

            $factory->setRandomGenerator($generator);
            $factory->setCodec($codec);
        }

        $old = UuidLib::getFactory();
        UuidLib::setFactory($factory);
        $output = UuidLib::uuid4();
        UuidLib::setFactory($old);

        return $output;
    }

    /**
     * Generate a Uuid5 string
     */
    public static function v5String(string $ns, string $name): string
    {
        return (string)UuidLib::uuid5($node, $clockSeq);
    }

    /**
     * Generate a Uuid5 object
     */
    public static function v5(string $ns, string $name): UuidInterface
    {
        return UuidLib::uuid5($ns, $name);
    }



    /**
     * Normalize a value to standard string
     */
    public static function normalize($value): ?string
    {
        $value = self::parse($value);

        if ($value === null) {
            return null;
        } else {
            return (string)$value;
        }
    }

    /**
     * Normalize a value to UuidInterface
     */
    public static function parse($value): ?UuidInterface
    {
        if ($value === null) {
            return null;
        } elseif ($value instanceof UuidInterface) {
            return $value;
        } elseif (is_string($value)) {
            if (strlen($value) === 16) {
                return UuidLib::fromBytes($value);
            } else {
                return UuidLib::fromString($value);
            }
        } elseif (is_int($value)) {
            return UuidLib::fromInteger($value);
        } else {
            throw Glitch::EInvalidArgument('Invalid Uuid input', null, $value);
        }
    }

    /**
     * Check is input is valid
     */
    public static function isValid(?string $input): bool
    {
        if ($input === null) {
            return false;
        }

        return UuidLib::isValid($input);
    }

    /**
     * Compare two values
     */
    public static function eq($a, $b): bool
    {
        try {
            $a = self::normalize($a);
            $b = self::normalize($b);
        } catch (\Throwable $e) {
            return false;
        }

        if ($a === null || $b === null) {
            return false;
        }

        return $a === $b;
    }


    /**
     * Get datetime from input
     */
    public static function getDate($input): ?Date
    {
        if (null === ($output = self::parse($input))) {
            return null;
        }

        try {
            return Date::parse($output->getDateTime());
        } catch (UnsupportedOperationException $e) {
            return null;
        }
    }


    /**
     * Convert input to byte string
     */
    public static function toBytes($input): ?string
    {
        if (null === ($output = self::parse($input))) {
            return null;
        }

        return $output->getBytes();
    }

    /**
     * Convert input to hex string
     */
    public static function toHex($input): ?string
    {
        if (null === ($output = self::parse($input))) {
            return null;
        }

        return $output->getHex();
    }
}
