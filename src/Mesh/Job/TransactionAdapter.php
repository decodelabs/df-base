<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Mesh\Job;

interface TransactionAdapter
{
    public function getTransactionId(): string;
    public function begin(): TransactionAdapter;
    public function commit(): TransactionAdapter;
    public function rollback(): TransactionAdapter;
}
