<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\ISource;
use Df\Opal\Query\Source\Manager;
use Df\Opal\Query\Source\Reference;

interface IParentAware extends IBuilder
{
    public function asSubQuery(IBuilder $parent, string $mode): IParentAware;

    public function setParentQuery(?IBuilder $parent): IParentAware;
    public function getParentQuery(): ?IBuilder;

    public function setSubQueryMode(?string $mode): IParentAware;
    public function getSubQueryMode(): ?string;

    public function getParentSourceManager(): ?Manager;
    public function getParentPrimarySourceReference(): ?Reference;
    public function getParentPrimarySourceAlias(): ?string;
    public function getParentPrimarySource(): ?ISource;
    public function isSourceDeepNested(Reference $reference): bool;
}
