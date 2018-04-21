<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\lang\constraint;

use df;
use df\lang;

trait TReadOnly
{
    protected $required = false;

    /**
     * Is this readOnly?
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Set readOnly
     */
    public function setReadOnly(bool $readOnly): IReadOnly
    {
        $this->readOnly = $readOnly;
        return $this;
    }
}
