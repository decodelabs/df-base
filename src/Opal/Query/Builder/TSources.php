<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;
use Df\Mesh\Job\ITransaction;
use Df\Mesh\Job\ITransactionAware;
use Df\Opal\Query\ISource;

trait TSources
{
    /**
     * Get source from primary ref
     */
    public function getPrimarySource(): ISource
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
    public function setTransaction(?ITransaction $transaction): ITransactionAware
    {
        $this->getSourceManager()->setTransaction($transaction);
        return $this;
    }

    /**
     * Get transaction from source manager
     */
    public function getTransaction(): ?ITransaction
    {
        return $this->getSourceManager()->getTransaction();
    }
}
