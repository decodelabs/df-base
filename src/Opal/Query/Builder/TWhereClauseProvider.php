<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\Clause\IWhere;
use Df\Opal\Query\Clause\IWhereFacade;
use Df\Opal\Query\Clause\TWhereFacade;
use Df\Opal\Query\Clause\WhereGroup;

trait TWhereClauseProvider
{
    use TWhereFacade;

    protected $prerequisites = [];

    /**
     * Create single level prerequisite
     */
    public function prerequisite(string $name, string $local, string $operator, $value): IWhereFacade
    {
        return $this->beginPrerequisite()
            ->where($local, $operator, $value)
            ->endPrerequisite($name);
    }

    /**
     * Create single level field prerequisite
     */
    public function prerequisiteField(string $name, string $local, string $operator, string $foreign): IWhereFacade
    {
        return $this->beginPrerequisite()
            ->whereField($local, $operator, $foreign)
            ->endPrerequisite($name);
    }

    /**
     * Being a prerequisite block
     */
    public function beginPrerequisite(string $name=null, callable $group=null): IWhereFacade
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
    public function addPrerequisite(string $name, IWhere $clause): IWhereFacade
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
    public function getPrerequisite(string $name): ?IWhere
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
    public function clearPrerequisites(): IWhereFacade
    {
        $this->prerequisites = [];
        return $this;
    }

    /**
     * Remove named prerequisite
     */
    public function removePrerequisite(string $name): IWhereFacade
    {
        unset($this->prerequisites[$name]);
        return $this;
    }
}
