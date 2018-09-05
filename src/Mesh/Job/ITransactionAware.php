<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Mesh\Job;

use Df;

interface ITransactionAware
{
    public function setTransaction(?ITransaction $transaction): ITransactionAware;
    public function getTransaction(): ?ITransaction;
}