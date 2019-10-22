<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Crypt;

interface ISymmetric
{
    public function encryptString(string $string): string;
    public function encryptData($data): string;
    public function encryptFile(string $inputFilePath, string $outputFilePath): void;
    public function checksumFile(string $filePath): string;

    public function decryptString(string $encrypted): string;
    public function decryptData(string $encrypted);
    public function decryptFile(string $inputFilePath, string $outputFilePath): void;
}
