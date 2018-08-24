<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Mesh\Job;

use Df;

interface ITransactionAdapter
{
    public function getTransactionId(): string;
    public function begin(): ITransactionAdapter;
    public function commit(): ITransactionAdapter;
    public function rollback(): ITransactionAdapter;
}
