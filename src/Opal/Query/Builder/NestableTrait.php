<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Nestable;

trait NestableTrait
{
    protected $nests = [];

    /**
     * Begin a nest instruction
     */
    public function nest(string ...$fields): Nest
    {
        return new Nest($this, $fields);
    }

    /**
     * Register nest instruction
     */
    public function addNest(Nest $nest): Nestable
    {
        $this->nests[$nest->getName()] = $nest;
        return $this;
    }

    /**
     * Get list of registered nests
     */
    public function getNests(): array
    {
        return $this->nests;
    }

    /**
     * Remove all nests
     */
    public function clearNests(): Nestable
    {
        $this->nests = [];
        return $this;
    }
}
