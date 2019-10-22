<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df;
use Df\Opal\Query\IClause;
use Df\Opal\Query\IField;
use Df\Opal\Query\Clause\TRepresentation;

use DecodeLabs\Glitch;

abstract class Base implements IClause
{
    use TRepresentation;

    const OP_EQ = '=';
    const OP_GT = '>';
    const OP_GTE = '>=';
    const OP_LT = '<';
    const OP_LTE = '<=';

    const OP_IN = 'in';
    const OP_BETWEEN = 'between';
    const OP_LIKE = 'like';
    const OP_CONTAINS = 'contains';
    const OP_BEGINS = 'begins';
    const OP_ENDS = 'ends';
    const OP_INCLUDES = 'includes';
    const OP_MATCHES = 'matches';

    protected $field;
    protected $operator = '=';
    protected $negate = false;
    protected $value;
    protected $preparedValue;
    protected $prepared = null;


    /**
     * Init with field, op and isOr
     */
    public function __construct(IField $field, string $operator, bool $or=false)
    {
        $this->setField($field);
        $this->setOperator($operator);
        $this->setOr($or);
    }


    /**
     * Set local clause field
     */
    public function setField(IField $field): IClause
    {
        // TODO: check for virtual

        $this->field = $field;
        return $this;
    }

    /**
     * Get local clause field
     */
    public function getField(): IField
    {
        return $this->field;
    }


    /**
     * Set operator
     */
    public function setOperator(string $operator): IClause
    {
        $this->operator = self::normalizeOperator($operator, $negate);
        $this->negate = $negate;
        return $this;
    }

    /**
     * Get operator
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Normalize operator
     */
    public function normalizeOperator(string $operator, ?bool &$negate=false): string
    {
        $negate = false;
        $operator = strtolower($operator);

        if (preg_match('/^(not |\!) *(.*)$/', $operator, $matches)) {
            $operator = $matches[2];
            $negate = true;
        }

        switch ($operator) {
            case self::OP_EQ:
            case self::OP_GT:
            case self::OP_LT:
            case self::OP_IN:
            case self::OP_BETWEEN:
            case self::OP_LIKE:
            case self::OP_CONTAINS:
            case self::OP_BEGINS:
            case self::OP_ENDS:
            case self::OP_MATCHES:
            case self::OP_INCLUDES:
                break;

            default:
                throw Glitch::EInvalidArgument(
                    'Operator '.$operator.' is not recognized'
                );
        }

        return $operator;
    }


    /**
     * Negate current operator
     */
    public function invert(): IClause
    {
        $this->negate = !$this->negate;
        return $this;
    }

    /**
     * Has this clause been inverted?
     */
    public function isNegated(): bool
    {
        return $this->negate;
    }
}
