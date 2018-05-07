<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\lang\Constraint;

use Df;

trait TDisableable
{
    protected $disabled = false;

    /**
     * Is this disabled?
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Set disabled
     */
    public function setDisabled(bool $disabled): IDisableable
    {
        $this->disabled = $disabled;
        return $this;
    }
}
