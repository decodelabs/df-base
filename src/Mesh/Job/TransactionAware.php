<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Mesh\Job;

interface TransactionAware
{
    public function setTransaction(?Transaction $transaction): TransactionAware;
    public function getTransaction(): ?Transaction;
}
