<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Mesh\Job;

use Df;

trait TTransactionAware
{
    protected $transaction;


    /**
     * Set a global transaction on this object
     */
    public function setTransaction(?ITransaction $transaction): ITransactionAware
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * Get the current transaction
     */
    public function getTransaction(): ?ITransaction
    {
        return $this->transaction;
    }
}
