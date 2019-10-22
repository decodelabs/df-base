<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Clause;

use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Builder\Select as SelectBuilder;
use Df\Opal\Query\IClause;
use Df\Opal\Query\IField;

use Df\Opal\Query\Clause\IFacade;
use Df\Opal\Query\Clause\Value;
use Df\Opal\Query\Clause\Field;
use Df\Opal\Query\Clause\Query;

class Factory
{
    protected $facade;

    /**
     * Init with query
     */
    public function __construct(IFacade $facade)
    {
        $this->facade = $facade;
    }

    /**
     * Create a value based clause
     */
    public function createValueClause(string $field, string $operator, $value, bool $or): IClause
    {
        $sourceManager = $this->facade->getSourceManager();
        $field = $sourceManager->findLocalField($field);

        return new Value($field, $operator, $value, $or);
    }

    /**
     * Create a field based clause
     */
    public function createFieldClause(string $field, string $operator, string $foreign, bool $or): IClause
    {
        $sourceManager = $this->facade->getSourceManager();
        $field = $sourceManager->findLocalField($field);

        $foreign = $sourceManager->findForeignField(
            $foreign,
            $field->getSourceReference()->getAlias()
        );

        return new Field($field, $operator, $foreign, $or);
    }

    /**
     * Create a IBuilder based clause
     */
    public function createQueryClause(string $field, string $operator, SelectBuilder $query, bool $or): IClause
    {
        $sourceManager = $this->facade->getSourceManager();
        $field = $sourceManager->findLocalField($field);

        return new Query($field, $operator, $query, $or);
    }
}
