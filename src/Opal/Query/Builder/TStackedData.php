<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;

trait TStackedData
{
    use TStacked;

    /**
     * Complete stacked subquery as list
     */
    public function asList(string $name, $field1, $field2=null): IStackedData
    {
        $keyField = $valueField = $processor = null;

        if (is_callable($field1)) {
            $processor = $field1;
        } elseif ($field1 !== null) {
            if ($field2 === null) {
                $valueField = $this->getSourceManager()->findForeignField($field1);
            } else {
                $keyField = $this->getSourceManager()->findForeignField($field1);
            }
        }

        if (is_callable($field2)) {
            $processor = $field2;
        } elseif ($field2 !== null) {
            $valueField = $this->getSourceManager()->findForeignField($field2);
        }

        return $this->registerStack($name, 'list', $keyField, $valueField, $processor);
    }

    /**
     * Complete stacked subquery as single value
     */
    public function asValue(string $name, $field=null): IStackedData
    {
        $valueField = $processor = null;

        if ($field === null) {
            $field = $name;
        }

        if (is_callable($field)) {
            $processor = $field;
        } else {
            $valueField = $this->getSourceManager()->findForeignField($field2);
        }

        return $this->registerStack($name, 'value', null, $valueField, $processor);
    }
}
