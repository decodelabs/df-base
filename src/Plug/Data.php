<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Plug;

use Df;
use Df\Core\IApp;
use Df\Plug\IHelper;

use Df\Opal\Query\IReadFacade;
use Df\Opal\Query\IWriteFacade;
use Df\Opal\Query\IBuilder;

use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Initiator\Fetch as FetchInitiator;
use Df\Opal\Query\Initiator\Union as UnionInitiator;

use Df\Opal\Query\Initiator\Insert as InsertInitiator;
use Df\Opal\Query\Initiator\BatchInsert as BatchInsertInitiator;
use Df\Opal\Query\Initiator\Replace as ReplaceInitiator;
use Df\Opal\Query\Initiator\BatchReplace as BatchReplaceInitiator;
use Df\Opal\Query\Initiator\Update as UpdateInitiator;
use Df\Opal\Query\Initiator\Delete as DeleteInitiator;

class Data implements
    IHelper,
    IReadFacade,
    IWriteFacade
{
    protected $app;

    /**
     * Init with app
     */
    public function __construct(IApp $app)
    {
        $this->app = $app;
    }

    /**
     * Start a query
     */
    public function select(string ...$fields): SelectInitiator
    {
        return new SelectInitiator($this->app, $fields);
    }

    /**
     * Start a distinct query
     */
    public function selectDistinct(string ...$fields): SelectInitiator
    {
        return new SelectInitiator($this->app, $fields, true);
    }


    /**
     *
     */
    public function union(string ...$fields): UnionInitiator
    {
        Df\incomplete();
    }

    /**
     *
     */
    public function fetch(): FetchInitiator
    {
        Df\incomplete();
    }



    /**
     *
     */
    public function insert(array $values): InsertInitiator
    {
        Df\incomplete();
    }

    /**
     *
     */
    public function batchInsert(array $rows=[]): BatchInsertInitiator
    {
        Df\incomplete();
    }

    /**
     *
     */
    public function replace(array $values): ReplaceInitiator
    {
        Df\incomplete();
    }

    /**
     *
     */
    public function batchReplace(array $rows=[]): BatchReplaceInitiator
    {
        Df\incomplete();
    }

    /**
     *
     */
    public function update(array $valueMap=null): UpdateInitiator
    {
        Df\incomplete();
    }

    /**
     *
     */
    public function delete(): DeleteInitiator
    {
        Df\incomplete();
    }
}
