<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Flex;

use Df;
use Df\Flex\Text;

class Formatter
{
    /**
     * Normalize words, convert words to upper
     */
    public static function name(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        return (string)(new Text($name))
            ->replace(['-', '_'], ' ')
            ->regexReplace('([^ ])([A-Z/])', '\\1 \\2')
            ->regexReplace('([/])([^ ])', '\\1 \\2')
            ->toTitleCase();
    }

    /**
     * Initialise name
     */
    public static function initials(?string $name, bool $extendShort=true): ?string
    {
        if (empty($name)) {
            return null;
        }

        $output = (string)(new Text($name))
            ->replace(['-', '_'], ' ')
            ->regexReplace('[^A-Za-z0-9\s]', '')
            ->regexReplace('([^ ])([A-Z])', '\\1 \\2')
            ->toTitleCase()
            ->regexReplace('[^A-Z0-9]', '');

        if ($extendShort && strlen($output) == 1) {
            $output .= (new Text($name))
                ->toAscii()
                ->replace(['a', 'e', 'i', 'o', 'u'], '')
                ->getChar(1);
        }

        return $output;
    }

    /**
     * Strip vowels from text
     */
    public static function consonants(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        return (string)(new Text($text))
            ->toAscii()
            ->regexReplace('[aeiou]+', '');
    }

    /**
     * Uppercase first, to ASCII, strip some chars
     */
    public static function label(?string $label): ?string
    {
        if (empty($label)) {
            return null;
        }

        return (string)(new Text($label))
            ->regexReplace('[-_./:]', ' ')
            ->regexReplace('([a-z])([A-Z])', '\\1 \\2')
            ->toLowerCase()
            ->firstToUpperCase();
    }

    /**
     * Convert to Id
     */
    public static function id(?string $id): ?string
    {
        if (empty($id)) {
            return null;
        }

        return (string)(new Text($id))
            ->toAscii()
            ->regexReplace('([^ ])([A-Z])', '\\1 \\2')
            ->replace(['-', '.', '+'], ' ')
            ->regexReplace('[^a-zA-Z0-9_ ]', '')
            ->toTitleCase()
            ->replace(' ', '');
    }

    /**
     * Format as PHP_CONSTANT
     */
    public static function constant(?string $constant): ?string
    {
        if (empty($constant)) {
            return null;
        }

        return (string)(new Text($constant))
            ->toAscii()
            ->regexReplace('[^a-zA-Z0-9]', ' ')
            ->regexReplace('([^ ])([A-Z])', '\\1 \\2')
            ->regexReplace('[^a-zA-Z0-9_ ]', '')
            ->trim()
            ->replace(' ', '_')
            ->replace('__', '_')
            ->toUpperCase();
    }

    /**
     * Convert to slug
     */
    public static function slug(?string $slug, string $allowedChars=''): ?string
    {
        if (empty($slug)) {
            return null;
        }

        return (string)(new Text($slug))
            ->toAscii()
            ->regexReplace('([a-z][a-z])([A-Z][a-z])', '\\1 \\2')
            ->toLowerCase()
            ->regexReplace('[\s_/.,:]', '-')
            ->regexReplace('[^a-z0-9_\-'.preg_quote($allowedChars).']', '')
            ->regexReplace('-+', '-')
            ->trim(' -');
    }

    /**
     * Convert to path format slug
     */
    public static function pathSlug(?string $slug, string $allowedChars=''): ?string
    {
        if (empty($slug)) {
            return null;
        }

        $parts = explode('/', $slug);

        foreach ($parts as $i => $part) {
            $part = self::slug($part, $allowedChars);

            if (empty($part)) {
                unset($parts[$i]);
                continue;
            }

            $parts[$i] = $part;
        }

        return implode('/', $parts);
    }

    /**
     * Remove non-filesystem compatible chars
     */
    public static function filename(?string $filename, bool $allowSpaces=false): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $output = (new Text($filename))
            ->toAscii()
            ->replace('/', '_')
            ->regexReplace('[\/\\?%*:|"<>]', '');

        if (!$allowSpaces) {
            $output->replace(' ', '-');
        }

        return (string)$output;
    }

    /**
     * Cap length of string, add ellipsis if needed
     */
    public static function shorten(?string $string, int $length, bool $rtl=false): ?string
    {
        if (empty($string)) {
            return null;
        }

        if ($length < 5) {
            $length = 5;
        }

        $output = (new Text($string));

        if ($output->getLength() > ($length - 1)) {
            if ($rtl) {
                $output = $output->slice(-($length - 1))
                    ->trimLeft('., ')
                    ->prepend('…');
            } else {
                $output = $output->slice(0, $length - 1)
                    ->trimRight('., ')
                    ->append('…');
            }
        }

        return (string)$output;
    }

    /**
     * Wrapper around Text::numericToAlpha
     */
    public static function numericToAlpha(?int $number): ?string
    {
        if ($number === null) {
            return null;
        }

        return (string)Text::numericToAlpha($number);
    }

    /**
     * Wrapper around alphaToNumeric
     */
    public static function alphaToNumeric(?string $string): ?int
    {
        if (empty($string)) {
            return null;
        }

        return (new Text($string))->alphaToNumeric();
    }

    /**
     * String to boolean
     */
    public static function toBoolean(string $text): bool
    {
        return (new Text($string))->toBoolean();
    }
}
