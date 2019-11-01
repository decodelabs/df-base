<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Mesh\Job;

interface Transaction
{
    public function isOpen(): bool;
    public function registerAdapter(TransactionAdapter $adapter): Transaction;

    public function begin(): Transaction;
    public function commit(): Transaction;
    public function rollback(): Transaction;
}
