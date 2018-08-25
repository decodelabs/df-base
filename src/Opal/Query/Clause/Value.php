<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df;
use Df\Opal\Query\IField;

class Value extends Base
{
    protected $value;

    /**
     * Init with field, op and isOr
     */
    public function __construct(IField $field, string $operator, $value, bool $or=false)
    {
        parent::__construct($field, $operator, $or);
        $this->value = $value;
    }

    /**
     * Get prepared value
     */
    public function getPreparedValue()
    {
        Df\incomplete();
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

        $value = $this->value;

        if (is_array($value)) {
            $value = '['.implode(',', $value).']';
        }

        return '`'.$this->field.'` '.$operator.' '.$this->value;
    }
}
