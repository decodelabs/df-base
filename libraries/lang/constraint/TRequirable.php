<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\constraint;

use df;
use df\lang;


trait TRequirable
{
    protected $required = false;

    /**
     * Is this required?
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set required
     */
    public function setRequired(bool $required): IRequirable
    {
        $this->required = $required;
        return $this;
    }
}
