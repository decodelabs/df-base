<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\data;

use df;
use df\data;

class Arr
{
    /**
     * Collapse multi-dimensional collections to flat array
     */
    public static function collapse(iterable $data, bool $unique=false, bool $removeNull=false): array
    {
        $output = [];
        $sort = SORT_STRING;

        foreach ($data as $key => $value) {
            if ($value === null && $removeNull) {
                continue;
            }

            if ($isIterable = is_iterable($value)) {
                $children = $value;
            }

            if ($isContainer = $value instanceof IValueProvider) {
                $value = $value->getValue();
            }

            if ((!$isIterable || $isContainer)
            && (!$removeNull || $value !== null)) {
                if (is_object($value)) {
                    $sort = SORT_REGULAR;
                }

                if (is_string($key)) {
                    $output[$key] = $value;
                } else {
                    $output[] = $value;
                }
            }

            if ($isIterable) {
                $output = array_merge($output, self::collapse(
                    $children,
                    $unique,
                    $removeNull
                ));
            }
        }

        if ($unique) {
            return array_unique($output, $sort);
        } else {
            return $output;
        }
    }


    /**
     * Generator, scanning all non-container nodes
     */
    public static function scanLeaves(iterable $data, bool $removeNull=false): \Generator
    {
        foreach ($data as $key => $value) {
            if ($isIterable = is_iterable($value)) {
                $children = $value;
            }

            if ($isContainer = $value instanceof IValueProvider) {
                $value = $value->getValue();
            }

            if ((!$isIterable || $isContainer)
            && (!$removeNull || $value !== null)) {
                yield $key => $value;
            }

            if ($isIterable) {
                yield from self::scanLeaves($children, $removeNull);
            }
        }
    }


    /**
     * Check array is associative
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }



    /**
     * Get first of a collection
     */
    public static function getFirst(iterable $data, callable $filter=null)
    {
        foreach ($data as $key => $item) {
            if ($filter !== null && !$filter($item, $key)) {
                continue;
            }

            return $item;
        }

        return null;
    }

    /**
     * Get last item in an array
     */
    public static function getLast(array $array, callable $filter=null)
    {
        if (!$filter) {
            return end($array);
        }

        return self::getFirst(array_reverse($array, true), $filter);
    }

    /**
     * Get random item in array
     */
    public static function getRandom(array $array)
    {
        if(empty($array)) {
            throw df\Error::EUnderflow('Cannot pick random, array is empty');
        }

        return $array[array_rand($array)];
    }

    /**
     * Get random subset from array
     */
    public static function getRandomSlice(array $array, int $number): array
    {
        $count = count($array);

        if($number < 1) {
            return [];
        }

        if($number > $count) {
            throw df\Error::EUnderflow('Cannot random slice '.$number.' items, only '.$count.' items in array');
        }

        return self::intersectKeys($array, array_rand($array, $number));
    }

    /**
     * Key base shuffling
     */
    public static function kshuffle(array $array): array
    {
        uksort($array, function() { return rand() > getrandmax() / 2; });
        return $array;
    }

    /**
     * Get subset based on key match
     */
    public static function intersectKeys(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Filter an array
     */
    public static function filter(array $array, callable $filter): array
    {
        return array_filter($array, $filter, ARRAY_FILTER_USE_BOTH);
    }


    /**
     * Re-coding of var_export for tidiness
     */
    public static function export(array $array): string
    {
        return self::exportLevel($array, 1);
    }

    private static function exportLevel(array $array, int $level): string
    {
        $output = '['."\n";

        $i = 0;
        $count = count($array);
        $isNumericIndex = true;

        foreach ($array as $key => $val) {
            if ($key !== $i++) {
                $isNumericIndex = false;
                break;
            }
        }

        $i = 0;

        foreach ($array as $key => $val) {
            $output .= str_repeat('    ', $level);

            if (!$isNumericIndex) {
                $output .= '\''.addslashes($key).'\' => ';
            }

            if (is_object($val) || is_null($val)) {
                $output .= 'null';
            } elseif (is_array($val)) {
                $output .= self::exportLevel($val, $level + 1);
            } elseif (is_int($val) || is_float($val)) {
                $output .= $val;
            } elseif (is_bool($val)) {
                if ($val) {
                    $output .= 'true';
                } else {
                    $output .= 'false';
                }
            } else {
                $output .= '\''.addslashes($val).'\'';
            }

            if (++$i < $count) {
                $output .= ',';
            }

            $output .= "\n";
        }

        $output .= str_repeat('    ', $level - 1).']';

        return $output;
    }
}
