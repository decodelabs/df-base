<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\lang\error;

use df;
use df\lang;

/**
 * Main root exception inheritance
 * This trait is automatically rolled into the generated exception
 * when using the Factory
 */
trait TError
{
    protected $http;
    protected $data;
    protected $rewind;
    protected $stackTrace;

    public function __construct($message, array $params=[])
    {
        parent::__construct(
            $message,
            $params['code'] ?? 0,
            $params['previous'] ?? null
        );

        if (isset($params['file'])) {
            $this->file = $params['file'];
        }

        if (isset($params['line'])) {
            $this->line = $params['line'];
        }

        unset($params['code'], $params['previous'], $params['file'], $params['line']);

        $this->data = $params['data'] ?? null;
        $this->rewind = $params['rewind'] ?? 0;

        if (isset($params['http'])) {
            $this->http = (int)$params['http'];
        }
    }

    /**
     * Set arbitrary data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Retrieve previously stored data
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Associate error with HTTP code
     */
    public function setHttpCode(?int $code)
    {
        $this->http = $code;
        return $this;
    }

    /**
     * Get associated HTTP code
     */
    public function getHttpCode(): ?int
    {
        return $this->http;
    }


    /**
     * Get first call from trace
     */
    public function getStackFrame(): lang\debug\StackFrame
    {
        return $this->getStackTrace()->getFirstCall();
    }

    /**
     * Generate a StackTrace object from Exception trace
     */
    public function getStackTrace(): lang\debug\StackTrace
    {
        if (!$this->stackTrace) {
            $this->stackTrace = lang\debug\StackTrace::createFromException($this, $this->rewind + 2);
        }

        return $this->stackTrace;
    }



    /**
     * Get debug info
     */
    public function __debugInfo(): array
    {
        $output = [
            'message' => $this->message,
            'code' => $this->code,
            'http' => $this->http
        ];


        // Data
        if ($this->data !== null) {
            $output['data'] = $this->data;
        }


        // Types
        $types = [];
        $class = new \ReflectionClass($this);

        while ($class = $class->getParentClass()) {
            $types[] = $class->getName();
        }

        $output['types'] = array_merge($types, array_values(class_implements($this)));
        $output['file'] = $this->file.' : '.$this->line;


        // Trace
        $output['trace'] = $this->getStackTrace();

        return $output;
    }
}
