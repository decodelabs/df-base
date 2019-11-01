<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Stackable;
use Df\Opal\Query\Builder\Stacked;

use Df\Opal\Query\Field;

use DecodeLabs\Glitch;

trait StackedTrait
{
    /**
     * Complete stacked subquery as single row
     */
    public function asOne(string $name): Stacked
    {
        return $this->registerStack($name, 'one');
    }

    /**
     * Complete stacked subquery as row set
     */
    public function asMany(string $name, string $keyField=null): Stacked
    {
        if ($keyField !== null) {
            $keyField = $this->getSourceManager()->findForeignField($keyField);
        }

        return $this->registerStack($name, 'many', $keyField);
    }

    /**
     * Create and register stacked subquery
     */
    protected function registerStack(string $name, string $mode, ?Field $keyField=null, ?Field $valueField=null, ?callable $processor=null): Stacked
    {
        if ($this->getSubQueryMode() !== 'stack') {
            throw Glitch::ELogic('Sub query is not in stack mode');
        }

        $stack = (new Stack($name, $this, $mode))
            ->setKeyField($keyField)
            ->setValueField($valueField)
            ->setProcessor($processor);

        $output = $this->getParentQuery();

        if (!$output instanceof Stackable) {
            throw Glitch::EUnexpectedValue('Parent query is not stackable', null, $output);
        }

        return $output->addStack($stack);
    }
}
