<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\IField;

trait TStacked
{
    /**
     * Complete stacked subquery as single row
     */
    public function asOne(string $name): IStacked
    {
        return $this->registerStack($name, 'one');
    }

    /**
     * Complete stacked subquery as row set
     */
    public function asMany(string $name, string $keyField=null): IStacked
    {
        if ($keyField !== null) {
            $keyField = $this->getSourceManager()->findForeignField($keyField);
        }

        return $this->registerStack($name, 'many', $keyField);
    }

    /**
     * Create and register stacked subquery
     */
    protected function registerStack(string $name, string $mode, ?IField $keyField, ?IField $valueField, ?callable $processor): IStacked
    {
        if ($this->getSubQueryMode() !== 'stack') {
            throw Df\Error::ELogic('Sub query is not in stack mode');
        }

        $stack = (new Stack($name, $this, $mode))
            ->setKeyField($keyField)
            ->setValueField($valueField)
            ->setProcessor($processor);

        $output = $this->getParentQuery();
        $output->addStack($stack);

        return $output;
    }
}
