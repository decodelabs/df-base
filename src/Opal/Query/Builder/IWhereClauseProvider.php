<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Clause\IWhere;
use Df\Opal\Query\Clause\IWhereFacade;

interface IWhereClauseProvider extends IBuilder, IWhereFacade
{
    public function prerequisite(string $name, string $local, string $operator, $value): IWhereFacade;
    public function prerequisiteField(string $name, string $local, string $operator, string $foreign): IWhereFacade;
    public function beginPrerequisite(string $name=null, callable $group=null): IWhereFacade;

    public function addPrerequisite(string $name, IWhere $clause): IWhereFacade;
    public function getPrerequisites(): array;
    public function getPrerequisite(string $name): ?IWhere;
    public function hasPrerequisites(): bool;
    public function hasPrerequisite(string $name): bool;
    public function clearPrerequisites(): IWhereFacade;
    public function removePrerequisite(string $name): IWhereFacade;
}
