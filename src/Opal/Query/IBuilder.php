<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df;

use Df\Mesh\Job\ITransactionAware;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Source\Manager;

interface IBuilder extends ITransactionAware
{
    public function getSourceManager(): Manager;
    public function getPrimarySourceReference(): Reference;
    public function getPrimarySource(): ISource;
}
