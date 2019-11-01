<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Clause\Representation\Where as WhereRepresentation;
use Df\Opal\Query\Clause\Provider\Where as RootWhereClauseProvider;
use Df\Opal\Query\Clause\Provider\WhereTrait as RootWhereClauseProviderTrait;
use Df\Opal\Query\Clause\Group\Where as WhereGroup;

trait WhereClauseProviderTrait
{
    use RootWhereClauseProviderTrait;

    protected $prerequisites = [];

    /**
     * Create single level prerequisite
     */
    public function prerequisite(string $name, string $local, string $operator, $value): RootWhereClauseProvider
    {
        $output = $this->beginPrerequisite();
        $output->where($local, $operator, $value);

        if (!$output instanceof WhereGroup) {
            throw Glitch::EUnexpectedValue('Parent query is not a where clause provider', null, $output);
        }

        return $output->endPrerequisite($name);
    }

    /**
     * Create single level field prerequisite
     */
    public function prerequisiteField(string $name, string $local, string $operator, string $foreign): RootWhereClauseProvider
    {
        $output = $this->beginPrerequisite();
        $output->whereField($local, $operator, $foreign);

        if (!$output instanceof WhereGroup) {
            throw Glitch::EUnexpectedValue('Parent query is not a where clause provider', null, $output);
        }

        return $output->endPrerequisite($name);
    }

    /**
     * Being a prerequisite block
     */
    public function beginPrerequisite(string $name=null, callable $group=null): RootWhereClauseProvider
    {
        if ($name === null) {
            $name = uniqid('prq_');
        }

        $output = new WhereGroup($this, false);
        $output->setPrerequisiteName($name);

        if ($group) {
            $group($output);
            return $output->endPrerequisite($name);
        } else {
            return $output;
        }
    }


    /**
     * Register a prerequisite clause
     */
    public function addPrerequisite(string $name, WhereRepresentation $clause): RootWhereClauseProvider
    {
        $this->prerequisites[$name] = $clause;
        return $this;
    }

    /**
     * Get list of prerequisites
     */
    public function getPrerequisites(): array
    {
        return $this->prerequisites;
    }

    /**
     * Get named prerequisite clause
     */
    public function getPrerequisite(string $name): ?WhereRepresentation
    {
        return $this->prerequisites[$name] ?? null;
    }

    /**
     * Have any prerequisites been defined?
     */
    public function hasPrerequisites(): bool
    {
        return !empty($this->prerequisites);
    }

    /**
     * Is named prerequisite defined?
     */
    public function hasPrerequisite(string $name): bool
    {
        return isset($this->prerequisites[$name]);
    }

    /**
     * Remove all prerequisites
     */
    public function clearPrerequisites(): RootWhereClauseProvider
    {
        $this->prerequisites = [];
        return $this;
    }

    /**
     * Remove named prerequisite
     */
    public function removePrerequisite(string $name): RootWhereClauseProvider
    {
        unset($this->prerequisites[$name]);
        return $this;
    }
}
