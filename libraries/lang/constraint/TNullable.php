<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace df\lang\constraint;

use df;

trait TNullable
{
    protected $nullable = false;

    /**
     * Is this nullable?
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Set nullable
     */
    public function setNullable(bool $nullable): INullable
    {
        $this->nullable = $nullable;
        return $this;
    }
}
