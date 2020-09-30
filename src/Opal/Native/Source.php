<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Native;

use Df\Mesh\Job\TransactionAdapter;
use Df\Opal\Query\Source\Composed as ComposedSource;

use DecodeLabs\Exceptional;

class Source implements ComposedSource, TransactionAdapter
{
    protected $name;
    protected $data;
    protected $fieldNames;
    protected $previousState;

    /**
     * Init with data
     */
    public function __construct(string $name, array $data)
    {
        $this->data = $data;

        if (preg_match('/[^a-zA-Z0-9_-]/', $name)) {
            throw Exceptional::InvalidArgument(
                'Source name must only contain alphanumerics, - and _'
            );
        }

        $this->name = $name;
    }

    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get query source id
     */
    public function getQuerySourceId(): string
    {
        return 'native://'.$this->name.'#'.uniqid();
    }

    /**
     * Get transaction id
     */
    public function getTransactionId(): string
    {
        return $this->getQuerySourceId();
    }

    /**
     * Get default alias
     */
    public function getDefaultQueryAlias(): string
    {
        return $this->name;
    }

    /**
     * Description
     */
    public function getFieldNames(): array
    {
        if ($this->fieldNames === null) {
            if (!isset($this->data[0])) {
                $this->fieldNames = [];
            } else {
                $this->fieldNames = array_keys($this->data[0]);
            }
        }

        return $this->fieldNames;
    }




    /**
     * Start a transaction
     */
    public function begin(): TransactionAdapter
    {
        $this->previousState = clone $this;
        return $this;
    }

    /**
     * Commit the current transaction
     */
    public function commit(): TransactionAdapter
    {
        if (!$this->previousState) {
            return $this;
        }

        if ($this->previousState->previousState) {
            $this->previousState = $this->previousState->previousState;
        }

        return $this;
    }

    /**
     * Revert back to previous state
     */
    public function rollback(): TransactionAdapter
    {
        if ($this->previousState) {
            $this->data = $this->previousState->data;

            if ($this->previousState->previousState) {
                $this->previousState = $this->previousState->previousState;
            }
        }

        return $this;
    }
}
