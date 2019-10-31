<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal;

use Df\Core\IApp;

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

use DecodeLabs\Glitch;

class Entry implements
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
        Glitch::incomplete();
    }

    /**
     *
     */
    public function fetch(): FetchInitiator
    {
        Glitch::incomplete();
    }



    /**
     *
     */
    public function insert(array $values): InsertInitiator
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function batchInsert(array $rows=[]): BatchInsertInitiator
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function replace(array $values): ReplaceInitiator
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function batchReplace(array $rows=[]): BatchReplaceInitiator
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function update(array $valueMap=null): UpdateInitiator
    {
        Glitch::incomplete();
    }

    /**
     *
     */
    public function delete(): DeleteInitiator
    {
        Glitch::incomplete();
    }
}
