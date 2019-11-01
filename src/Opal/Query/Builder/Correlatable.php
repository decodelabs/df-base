<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Select;
use Df\Opal\Query\Initiator\Select as SelectInitiator;

interface Correlatable extends Builder
{
    public function correlate(string $relationField): SelectInitiator;
    public function correlateRelation(string $relationField, string $field): SelectInitiator;

    public function countRelation(string $relationField, string $alias=null): Correlatable;
    public function beginCountRelation(string $relationField): SelectInitiator;
    public function hasRelation(string $relationField, string $alias=null): Correlatable;
    public function beginHasRelation(string $relationField): SelectInitiator;

    public function addCorrelation(Select $subQuery, string $alias=null): Correlatable;
    public function getCorrelations(): array;
}
