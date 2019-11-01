<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder\ICorrelated;
use Df\Opal\Query\Builder\ICorrelatable;

use DecodeLabs\Glitch;

trait TCorrelated
{
    /**
     * End correlation
     */
    public function endCorrelation(string $alias=null): ICorrelatable
    {
        if ($this->getSubQueryMode() !== 'correlation') {
            throw Glitch::ELogic('Select query is not a correlation');
        }

        if (!$parent = $this->getParentQuery()) {
            throw Glitch::ELogic('Query does not have a parent to be aliased into');
        }

        if (!$parent instanceof ICorrelatable) {
            throw Glitch::ELogic('Parent query is not correlatable');
        }

        $parent->addCorrelation($this, $alias);
        return $parent;
    }
}
