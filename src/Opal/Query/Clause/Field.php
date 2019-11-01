<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Field as FieldInterface;
use Df\Opal\Query\Field\Named as NamedField;

use DecodeLabs\Glitch;

class Field extends Base
{
    protected $foreign;

    /**
     * Init with field, op and isOr
     */
    public function __construct(FieldInterface $field, string $operator, FieldInterface $foreign, bool $or=false)
    {
        parent::__construct($field, $operator, $or);
        $this->foreign = $foreign;
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

        if ($this->field instanceof NamedField) {
            $local = '`'.$this->field.'`';
        } else {
            $local = '*'.$this->field->getAlias();
        }

        if ($this->field instanceof NamedField) {
            $foreign = '`'.$this->foreign.'`';
        } else {
            $foreign = '*'.$this->foreign->getAlias();
        }

        return $local.' '.$operator.' '.$foreign;
    }
}
