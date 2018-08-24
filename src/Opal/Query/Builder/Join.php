<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;

use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use Df\Opal\Query\IBuilder;

class Join implements
    IBuilder,
    IParentAware
{
    use TSources;
    use TParentAware;

    protected $type;
    protected $sourceReference;

    /**
     * Init with parent
     */
    public function __construct(IBuilder $parentQuery, Reference $reference, string $type='inner')
    {
        $this->subQueryMode = 'join';
        $this->parentQuery = $parentQuery;
        $this->sourceReference = $reference;
        $this->setType($type);
    }

    /**
     * Get source manager
     */
    public function getSourceManager(): Manager
    {
        return $this->parentQuery->getSourceManager();
    }

    /**
     * Get main source ref
     */
    public function getPrimarySourceReference(): Reference
    {
        return $this->sourceReference;
    }

    /**
     * Set join type
     */
    public function setType(string $type): Join
    {
        switch ($type) {
            case 'inner':
            case 'left':
            case 'right':
            case 'outer':
                $this->type = $type;
                break;

            default:
                throw Df\Error::EInvalidArgument(
                    'Unknown join type '.$type
                );
        }

        return $this;
    }

    /**
     * Get join type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get source alias
     */
    public function getSourceAlias(): string
    {
        return $this->sourceReference->getAlias();
    }


    /**
     * Complete join with alias and return parent
     */
    public function as(string $alias): IBuilder
    {
        return $this->endJoin($alias);
    }

    /**
     * Finalize join
     */
    public function endJoin(string $alias=null): IBuilder
    {
        $this->parentQuery->addJoin($this, $alias);
        return $this->parentQuery;
    }



    /**
     * Render as pseudo SQL string
     */
    public function __toString(): string
    {
        $output = strtoupper($this->type).' JOIN '.$this->sourceReference;

        // TODO: on clauses

        return $output;
    }
}
