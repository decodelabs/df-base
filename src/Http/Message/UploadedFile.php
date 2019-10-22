<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Http\Message;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

use DecodeLabs\Glitch;

class UploadedFile implements UploadedFileInterface
{
    const ERRORS = [
        UPLOAD_ERR_OK => 'The file uploaded successfully',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was not fully completed',
        UPLOAD_ERR_NO_FILE => 'The file was not uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Upload temp dir was not available',
        UPLOAD_ERR_CANT_WRITE => 'Upload temp dir was not writable',
        UPLOAD_ERR_EXTENSION => 'The file upload was cancelled by a PHP extension'
    ];

    protected $size;
    protected $filename;
    protected $type;
    protected $error;

    protected $file;
    protected $stream;

    protected $moved = false;


    /**
     * Init the file
     */
    public function __construct(
        $file,
        ?int $size,
        int $error,
        ?string $filename=null,
        ?string $type=null
    ) {
        if ($error === UPLOAD_ERR_OK) {
            if (is_string($file)) {
                $this->file = $file;
            } else {
                if (!$file instanceof StreamInterface) {
                    $file = new Stream($file);
                }

                $this->stream = $file;
            }
        }

        $this->size = $size;

        if (!isset(static::ERRORS[$error])) {
            throw Glitch::EInvalidArgument(
                'Invalid uploaded file status: '.$error
            );
        }

        $this->error = $error;
        $this->filename = $filename;
        $this->type = $type;
    }


    /**
     * Get size of uploaded file
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Get file name
     */
    public function getClientFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Get type sent with file
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /**
     * Get one of UPLOAD_ERR_XXX codes
     */
    public function getError(): int
    {
        return $this->error;
    }


    /**
     * Move uploaded file to destination
     */
    public function moveTo($targetPath): void
    {
        if ($this->moved) {
            throw Glitch::ERuntime(
                'File has already been moved'
            );
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw Glitch::ERuntime(
                'Cannot move file: '.static::ERRORS[$this->error]
            );
        }

        if (empty($targetPath = (string)$targetPath)) {
            throw Glitch::EInvalidArgument(
                'Invalid upload file target path'
            );
        }

        $targetDir = dirname($targetPath);

        if (!is_dir($targetDir) || !is_writable($targetDir)) {
            throw Glitch::ERuntime(
                'Target directory doesn\'t exist: '.$targetDir
            );
        }

        $sapi = PHP_SAPI;

        if (empty($sapi) || 0 === strpos($sapi, 'cli') || !$this->file) {
            $this->writeFile($targetPath);
        } else {
            if (false === move_uploaded_file($this->file, $targetPath)) {
                throw Glitch::ERuntime(
                    'Moving uploaded file failed'
                );
            }
        }

        $this->moved = true;
    }

    /**
     * Write non-sapi file
     */
    protected function writeFile(string $targetPath): void
    {
        if (false === ($fp = fopen($targetPath, 'wb+'))) {
            throw Glitch::ERuntime(
                'Target path is not writable'
            );
        }

        $stream = $this->getStream();
        $stream->rewind();

        while (!$stream->eof()) {
            fwrite($fp, $stream->read(4096));
        }

        fclose($fp);
    }

    /**
     * Get a stream representation of the file
     */
    public function getStream(): StreamInterface
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw Glitch::ERuntime(
                'Stream not available: '.static::ERRORS[$this->error]
            );
        }

        if ($this->moved) {
            throw Glitch::ERuntime(
                'Stream not available, file has already been moved'
            );
        }

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $this->stream = new Stream($this->file);
        return $this->stream;
    }
}
