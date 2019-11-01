<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\Builder;
use Df\Opal\Query\Builder\Select as SelectBuilder;
use Df\Opal\Query\Clause;

use Df\Opal\Query\Clause\Provider;
use Df\Opal\Query\Clause\Value as ValueClause;
use Df\Opal\Query\Clause\Field as FieldClause;
use Df\Opal\Query\Clause\Query as QueryClause;

class Factory
{
    protected $facade;

    /**
     * Init with query
     */
    public function __construct(Provider $facade)
    {
        $this->facade = $facade;
    }

    /**
     * Create a value based clause
     */
    public function createValueClause(string $field, string $operator, $value, bool $or): Clause
    {
        $sourceManager = $this->facade->getSourceManager();
        $field = $sourceManager->findLocalField($field);

        return new ValueClause($field, $operator, $value, $or);
    }

    /**
     * Create a field based clause
     */
    public function createFieldClause(string $field, string $operator, string $foreign, bool $or): Clause
    {
        $sourceManager = $this->facade->getSourceManager();
        $field = $sourceManager->findLocalField($field);

        $foreign = $sourceManager->findForeignField(
            $foreign,
            $field->getSourceReference()->getAlias()
        );

        return new FieldClause($field, $operator, $foreign, $or);
    }

    /**
     * Create a Builder based clause
     */
    public function createQueryClause(string $field, string $operator, SelectBuilder $query, bool $or): Clause
    {
        $sourceManager = $this->facade->getSourceManager();
        $field = $sourceManager->findLocalField($field);

        return new QueryClause($field, $operator, $query, $or);
    }
}
