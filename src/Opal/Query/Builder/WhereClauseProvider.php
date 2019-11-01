<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Clause\Representation\Where as WhereRepresentation;
use Df\Opal\Query\Clause\Provider\Where as RootWhereClauseProvider;

interface WhereClauseProvider extends Builder, RootWhereClauseProvider
{
    public function prerequisite(string $name, string $local, string $operator, $value): RootWhereClauseProvider;
    public function prerequisiteField(string $name, string $local, string $operator, string $foreign): RootWhereClauseProvider;
    public function beginPrerequisite(string $name=null, callable $group=null): RootWhereClauseProvider;

    public function addPrerequisite(string $name, WhereRepresentation $clause): RootWhereClauseProvider;
    public function getPrerequisites(): array;
    public function getPrerequisite(string $name): ?WhereRepresentation;
    public function hasPrerequisites(): bool;
    public function hasPrerequisite(string $name): bool;
    public function clearPrerequisites(): RootWhereClauseProvider;
    public function removePrerequisite(string $name): RootWhereClauseProvider;
}
