<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Lang\Constraint;

use Df;

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
