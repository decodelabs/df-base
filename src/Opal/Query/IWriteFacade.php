<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df\Opal\Query\Initiator\Insert as InsertInitiator;
use Df\Opal\Query\Initiator\BatchInsert as BatchInsertInitiator;
use Df\Opal\Query\Initiator\Replace as ReplaceInitiator;
use Df\Opal\Query\Initiator\BatchReplace as BatchReplaceInitiator;
use Df\Opal\Query\Initiator\Update as UpdateInitiator;
use Df\Opal\Query\Initiator\Delete as DeleteInitiator;

interface IWriteFacade
{
    /*
    public function insert(array $values): InsertInitiator;
    public function batchInsert(array $rows=[]): BatchInsertInitiator;
    public function replace(array $values): ReplaceInitiator;
    public function batchReplace(array $rows=[]): BatchReplaceInitiator;
    public function update(array $valueMap=null): UpdateInitiator;
    public function delete(): DeleteInitiator;
    */
}
