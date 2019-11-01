<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Source;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

interface ParentAware extends Builder
{
    public function asSubQuery(Builder $parent, string $mode, ?callable $applicator=null): ParentAware;

    public function setParentQuery(?Builder $parent): ParentAware;
    public function getParentQuery(): ?Builder;

    public function setSubQueryMode(?string $mode, ?callable $applicator=null): ParentAware;
    public function getSubQueryMode(): ?string;

    public function getParentSourceManager(): ?Manager;
    public function getParentPrimarySourceReference(): ?Reference;
    public function getParentPrimarySourceAlias(): ?string;
    public function getParentPrimarySource(): ?Source;
    public function isSourceDeepNested(Reference $reference): bool;
}
