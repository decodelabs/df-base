<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df;
use Df\Opal\Query\IField;
use Df\Opal\Query\Field\INamed as INamedField;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Builder\Select as SelectBuilder;

class Query extends Base
{
    protected $query;

    /**
     * Init with field, op and isOr
     */
    public function __construct(IField $field, string $operator, SelectBuilder $query, bool $or=false)
    {
        parent::__construct($field, $operator, $or);
        $this->query = $query;
    }

    /**
     * Get prepared value
     */
    public function getPreparedValue()
    {
        Glitch::incomplete();
    }

    /**
     * Render to pseudo SQL string
     */
    public function __toString(): string
    {
        $operator = strtoupper($this->operator);

        if ($this->negate) {
            $operator = '!'.$operator;
        }

        if ($this->field instanceof INamedField) {
            $fieldName = '`'.$this->field.'`';
        } else {
            $fieldName = '*'.$this->field->getAlias();
        }

        $output = $fieldName.' '.$operator.' (';
        $output .= str_replace("\n", "\n    ", $this->query)."\n";
        $output .= '    )';

        return $output;
    }
}
