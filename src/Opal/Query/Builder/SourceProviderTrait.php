<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Mesh\Job\Transaction;
use Df\Mesh\Job\TransactionAware;
use Df\Opal\Query\Source;

trait SourceProviderTrait
{
    /**
     * Get source from primary ref
     */
    public function getPrimarySource(): Source
    {
        return $this->getPrimarySourceReference()->getSource();
    }

    /**
     * Get alias of primary source
     */
    public function getPrimarySourceAlias(): string
    {
        return $this->getPrimarySourceReference()->getAlias();
    }

    /**
     * Pass transaction through to source mananger
     */
    public function setTransaction(?Transaction $transaction): TransactionAware
    {
        $this->getSourceManager()->setTransaction($transaction);
        return $this;
    }

    /**
     * Get transaction from source manager
     */
    public function getTransaction(): ?Transaction
    {
        return $this->getSourceManager()->getTransaction();
    }
}
