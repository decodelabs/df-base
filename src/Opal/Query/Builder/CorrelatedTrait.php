<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder\Correlatable;

use DecodeLabs\Exceptional;

trait CorrelatedTrait
{
    /**
     * End correlation
     */
    public function endCorrelation(string $alias=null): Correlatable
    {
        if ($this->getSubQueryMode() !== 'correlation') {
            throw Exceptional::Logic(
                'Select query is not a correlation'
            );
        }

        if (!$parent = $this->getParentQuery()) {
            throw Exceptional::Logic(
                'Query does not have a parent to be aliased into'
            );
        }

        if (!$parent instanceof Correlatable) {
            throw Exceptional::Logic(
                'Parent query is not correlatable'
            );
        }

        $parent->addCorrelation($this, $alias);
        return $parent;
    }
}
