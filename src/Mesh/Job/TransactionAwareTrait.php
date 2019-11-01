<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Mesh\Job;

trait TransactionAwareTrait
{
    protected $transaction;


    /**
     * Set a global transaction on this object
     */
    public function setTransaction(?Transaction $transaction): TransactionAware
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * Get the current transaction
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }
}
