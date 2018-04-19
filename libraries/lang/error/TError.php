<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
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

/*
    public function getStackCall(): core\debug\IStackCall
    {
        return $this->getStackTrace()->getFirstCall();
    }

    public function getStackTrace(): core\debug\IStackTrace
    {
        if (!$this->_stackTrace) {
            $this->_stackTrace = core\debug\StackTrace::factory($this->_rewind + 1, $this->getTrace());
        }

        return $this->_stackTrace;
    }
    */
}
