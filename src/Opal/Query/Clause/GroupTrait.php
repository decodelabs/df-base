<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Clause\Provider;
use Df\Opal\Query\Clause\RepresentationTrait;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

use DecodeLabs\Exceptional;

trait GroupTrait
{
    use RepresentationTrait;

    protected $parent;
    protected $parentQuery;


    /**
     * Init with parent
     */
    public function __construct(Provider $parent, bool $or=false)
    {
        $this->parent = $parent;
        $this->setOr($or);
    }

    /**
     * Get parent (group or query)
     */
    public function getParent(): Provider
    {
        return $this->parent;
    }

    /**
     * Get parent query
     */
    public function getQuery(): Builder
    {
        if ($this->parentQuery === null) {
            $target = $this;

            while (!$target instanceof Builder) {
                $test = $target->getParent();

                if ($test === $target) {
                    throw Exceptional::Logic(
                        'Recursive clause group detected'
                    );
                }

                if ($test === null) {
                    throw Exceptional::Logic(
                        'Clause group is not contained in a query'
                    );
                }

                $target = $test;
            }

            $this->parentQuery = $target;
        }

        return $this->parentQuery;
    }


    /**
     * Get source manager from parent query
     */
    public function getSourceManager(): Manager
    {
        return $this->getQuery()->getSourceManager();
    }

    /**
     * Get primary source reference from parent query
     */
    public function getPrimarySourceReference(): Reference
    {
        return $this->getQuery()->getPrimarySourceReference();
    }

    /**
     * Get primary source alias
     */
    public function getPrimarySourceAlias(): string
    {
        return $this->getQuery()->getPrimarySourceAlias();
    }


    /**
     * Render as pseudo SQL string
     */
    public function __toString(): string
    {
        $clauses = $this->toArray();

        if (count($clauses) == 0) {
            return '(VOID)';
        }

        if (count($clauses) == 1) {
            return (string)$clauses[0];
        }

        $output = '(';
        $first = true;

        foreach ($clauses as $clause) {
            if ($first) {
                $first = false;
            } else {
                $output .= $clause->isOr() ? ' || ' : ' && ';
            }

            $output .= "\n".'    '.str_replace("\n", "\n  ", $clause);
        }

        $output .= "\n".'  )';
        return $output;
    }
}
