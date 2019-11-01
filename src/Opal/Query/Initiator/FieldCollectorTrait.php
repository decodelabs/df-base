<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df\Opal\Query\Initiator\FieldCollector;

use DecodeLabs\Collections\ArrayUtils;

trait FieldCollectorTrait
{
    protected $fields = [];

    /**
     * Import field array
     */
    protected function importFields(array $fields): void
    {
        $this->addFields(...ArrayUtils::collapse($fields, false, true, true));
    }

    /**
     * Replace fields
     */
    public function setFields(string ...$fields): FieldCollector
    {
        $this->fields = [];
        return $this->addFields(...$fields);
    }

    /**
     * Add fields to list
     */
    public function addFields(string ...$fields): FieldCollector
    {
        $this->fields = array_unique(array_merge($this->fields, $fields));
        return $this;
    }

    /**
     * Get field list
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Clear all fields
     */
    public function clearFields(): FieldCollector
    {
        $this->fields = [];
        return $this;
    }
}
