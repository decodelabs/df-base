<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df\Mesh\Job\TransactionAware;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Source\Manager;

interface Builder extends TransactionAware
{
    public function getSourceManager(): Manager;
    public function getPrimarySourceReference(): Reference;
    public function getPrimarySource(): Source;
    public function getPrimarySourceAlias(): string;
    public function __toString(): string;
}
