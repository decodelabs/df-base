<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Mesh\Job;

use Df;

interface ITransaction
{
    public function isOpen(): bool;
    public function registerAdapter(ITransactionAdapter $adapter): ITransaction;

    public function begin(): ITransaction;
    public function commit(): ITransaction;
    public function rollback(): ITransaction;
}
