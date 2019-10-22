<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Crypt\Symmetric;

use Df;
use Df\Core\Crypt\ISymmetric;

use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\File;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;

use DecodeLabs\Glitch;

class Halite implements ISymmetric
{
    protected $keyPath;
    private $key;


    /**
     * Init with key path
     */
    public function __construct(string $keyPath)
    {
        $this->keyPath = $keyPath;
    }


    /**
     * Generate encryption key
     */
    public function generateKeyFile(): void
    {
        if ($this->keyFileExists()) {
            throw Glitch::ERuntime(
                'Key file already exists at keyPath'
            );
        }

        $dir = realpath(dirname($this->keyPath));

        if ($dir === false || !is_dir($dir) || !is_writable($dir)) {
            throw Glitch::{'ERuntime,EIo'}(
                'Cannot write encryption key to keyPath'
            );
        }

        $this->key = KeyFactory::generateEncryptionKey();
        KeyFactory::save($this->key, $this->keyPath);
    }

    /**
     * Key file exists
     */
    public function keyFileExists(): bool
    {
        return is_file($this->keyPath);
    }


    /**
     * Encrypt using loaded key
     */
    public function encryptString(string $string): string
    {
        $message = new HiddenString($string);
        return Crypto::encrypt($message, $this->loadKey());
    }

    /**
     * Convert arbitrary data to serialized string, pass to encryptString
     */
    public function encryptData($data): string
    {
        return $this->encryptString(serialize($data));
    }


    /**
     * Write $outputFilePath with encrypted version of file at $inputFilePath
     */
    public function encryptFile(string $inputFilePath, string $outputFilePath): void
    {
        if (!is_file($inputFilePath) || !is_readable($inputFilePath)) {
            throw Glitch::{'ENotFound,EIo'}(
                'File to encrypt could not be found',
                null,
                $inputFilePath
            );
        }

        $dir = realpath(dirname($outputFilePath));

        if ($dir === false || !is_dir($dir) || !is_writable($dir)) {
            throw Glitch::{'ERuntime,EIo'}(
                'Cannot write encryped file to $outputFilePath',
                null,
                $outputFilePath
            );
        }

        touch($outputFilePath);
        File::encrypt($inputFilePath, $outputFilePath, $this->loadKey());
    }

    /**
     * Generate a checksum hash from file
     */
    public function checksumFile(string $filePath): string
    {
        if (!is_file($filePath)) {
            throw Glitch::{'ENotFound,EIo'}(
                'File to checksum could not be found',
                null,
                $filePath
            );
        }

        return File::checksum($filePath);
    }



    /**
     * Decrypt using loaded key
     */
    public function decryptString(string $encrypted): string
    {
        return Crypto::decrypt($encrypted, $this->loadKey())->getString();
    }

    /**
     * Decrypt with decryptString, unserialize
     */
    public function decryptData(string $encrypted)
    {
        $output = $this->decryptString($encrypted);
        return unserialize($output);
    }

    /**
     * Write $outputFilePath with decrypted version of file at $inputFilePath
     */
    public function decryptFile(string $inputFilePath, string $outputFilePath): void
    {
        if (!is_file($inputFilePath)) {
            throw Glitch::{'ENotFound,EIo'}(
                'File to decrypt could not be found',
                null,
                $inputFilePath
            );
        }

        $dir = realpath(dirname($outputFilePath));

        if ($dir === false || !is_dir($dir) || !is_writable($dir)) {
            throw Glitch::{'ERuntime,EIo'}(
                'Cannot write decryped file to $outputFilePath',
                null,
                $outputFilePath
            );
        }

        touch($outputFilePath);
        File::decrypt($inputFilePath, $outputFilePath, $this->loadKey());
    }




    /**
     * Load encryption key
     */
    protected function loadKey(): EncryptionKey
    {
        if (!$this->key) {
            $this->key = KeyFactory::loadEncryptionKey($this->keyPath);
        }

        return $this->key;
    }
}
