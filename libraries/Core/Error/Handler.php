<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\core\error;

use df;

class Handler implements IHandler
{
    /**
     * Globally register handler
     */
    public static function register(IHandler $handler): void
    {
        set_error_handler([$handler, 'handleError']);
        set_exception_handler([$handler, 'handleException']);
        register_shutdown_function([$handler, 'handleShutdown']);
    }



    /**
     * Default ErrorException wrapper
     */
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!$current = error_reporting()) {
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Last-ditch catch-all for exceptions
     */
    public function handleException(\Throwable $exception): void
    {
        try {
            $app = df\app();
            $app->bindOnce(IReporter::class, namespace\reporter\Dump::class);
            $reporter = $app[IReporter::class];
        } catch (\Throwable $e) {
            dd($e, $exception);
        }

        $reporter->reportException($exception);
    }

    /**
     * Try and do something about fatal errors after shutdown
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error && self::isLevelFatal($error['type'])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * Is this error code fatal?
     */
    public function isLevelFatal(int $level): bool
    {
        $errors = E_ERROR;
        $errors |= E_PARSE;
        $errors |= E_CORE_ERROR;
        $errors |= E_CORE_WARNING;
        $errors |= E_COMPILE_ERROR;
        $errors |= E_COMPILE_WARNING;

        return ($level & $errors) > 0;
    }
}
