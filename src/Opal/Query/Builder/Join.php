<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Source;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\SourceProviderTrait;
use Df\Opal\Query\Builder\ParentAware;
use Df\Opal\Query\Builder\ParentAwareTrait;
use Df\Opal\Query\Builder\WhereClauseProvider;
use Df\Opal\Query\Builder\WhereClauseProviderTrait;
use Df\Opal\Query\Builder\RelationInspectorTrait;
use Df\Opal\Query\Clause\Group\Where as WhereGroup;

use DecodeLabs\Exceptional;

class Join implements
    Builder,
    ParentAware,
    WhereClauseProvider
{
    use SourceProviderTrait;
    use ParentAwareTrait;
    use WhereClauseProviderTrait;
    use RelationInspectorTrait;

    protected $type;
    protected $sourceReference;

    /**
     * Init with parent
     */
    public function __construct(Builder $parentQuery, Reference $reference, string $type='inner')
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
                throw Exceptional::InvalidArgument(
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
    public function as(string $alias): Builder
    {
        return $this->endJoin($alias);
    }

    /**
     * Finalize join
     */
    public function endJoin(string $alias=null): Builder
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

        if (!empty($where = $this->getWhereClauses())) {
            $group = new WhereGroup($this, false, $where);
            $output .= "\n".'  ON '.$group;
        }

        return $output;
    }
}
