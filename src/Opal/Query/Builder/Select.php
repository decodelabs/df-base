<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

class Select implements IBuilder
{
    protected $sourceManager;
    protected $sourceReference;

    /**
     * Init with manager and reference
     */
    public function __construct(Manager $manager, Reference $reference)
    {
        $this->sourceManager = $manager;
        $this->sourceReference = $reference;
    }
}
