<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Mesh\Job;

class Transaction implements ITransaction
{
    protected $open = true;
    protected $adapters = [];

    /**
     * Init with open state
     */
    public function __construct(bool $open=true)
    {
        $this->open = $open;
    }

    /**
     * Is this transaction open?
     */
    public function isOpen(): bool
    {
        return $this->open;
    }

    /**
     * Add an adapter to the list
     */
    public function registerAdapter(ITransactionAdapter $adapter): ITransaction
    {
        $id = $adapter->getTransactionId();

        if (!isset($this->adapters[$id])) {
            $this->adapters[$id] = $adapter;

            if ($this->open) {
                $adapter->begin();
            }
        }

        return $this;
    }


    /**
     * Start the transaction frame
     */
    public function begin(): ITransaction
    {
        if (!$this->open) {
            foreach ($this->adapters as $adapter) {
                $adapter->begin();
            }

            $this->open = true;
        }

        return $this;
    }

    /**
     * Apply changes to all adapters
     */
    public function commit(): ITransaction
    {
        if ($this->open) {
            foreach ($this->adapters as $adapter) {
                $adapter->commit();
            }

            $this->open = false;
        }

        return $this;
    }

    /**
     * Undo changes on all adapters
     */
    public function rollback(): ITransaction
    {
        if ($this->open) {
            foreach ($this->adapters as $adapter) {
                $adapter->rollback();
            }

            $this->open = false;
        }

        return $this;
    }
}
