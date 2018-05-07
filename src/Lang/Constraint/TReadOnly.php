<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Lang\Constraint;

use Df;

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
