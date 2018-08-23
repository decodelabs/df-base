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

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Initiator\Select as SelectInitiator;
use Df\Opal\Query\Builder\Select as SelectBuilder;

class Data implements IHelper
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
}
