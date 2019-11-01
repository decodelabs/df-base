<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df\Core\IApp;
use Df\Mesh\Job\TransactionAwareTrait;

use Df\Opal\Query\Initiator;
use Df\Opal\Query\Initiator\FieldCollector;
use Df\Opal\Query\Initiator\FieldCollectorTrait;
use Df\Opal\Query\Initiator\FromSource;
use Df\Opal\Query\Initiator\FromSourceTrait;
use Df\Opal\Query\Builder;
use Df\Opal\Query\Source\Manager as SourceManager;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Builder\Join as JoinBuilder;

use DecodeLabs\Glitch;

class Join implements
    Initiator,
    FieldCollector,
    FromSource
{
    use FieldCollectorTrait;
    use TransactionAwareTrait;
    use FromSourceTrait;

    protected $type = 'inner';
    protected $parentQuery;
    protected $app;

    /**
     * Init with fields and distinct
     */
    public function __construct(Builder $parentQuery, array $fields, string $type='inner')
    {
        $this->app = $parentQuery->getSourceManager()->getApp();
        $this->parentQuery = $parentQuery;
        $this->importFields($fields);
        $this->setType($type);
    }

    /**
     * Set join type
     */
    public function setType(string $type): Join
    {
        switch ($type) {
            case 'inner':
            case 'left':
            case 'right':
            case 'outer':
                $this->type = $type;
                break;

            default:
                throw Glitch::EInvalidArgument(
                    'Unknown join type '.$type
                );
        }

        return $this;
    }

    /**
     * Get join type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get parent query
     */
    public function getParentQuery(): Builder
    {
        return $this->parentQuery;
    }



    /**
     * Set source and alias
     */
    public function from($source, string $alias=null): Builder
    {
        $manager = $this->parentQuery->getSourceManager();
        $source = $manager->normalizeSource($source);
        $reference = new Reference($source, $alias);
        $manager->addReference($reference);

        foreach ($this->fields as $field) {
            $reference->selectField($field);
        }

        return new JoinBuilder($this->parentQuery, $reference, $this->type);
    }
}
