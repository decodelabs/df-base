<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Initiator;

use Df;
use Df\Core\IApp;

use Df\Mesh\Job\TTransactionAware;

use Df\Opal\Query\IInitiator;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\Source\Manager as SourceManager;
use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Builder\Join as JoinBuilder;

use DecodeLabs\Glitch;

class Join implements
    IInitiator,
    IFieldCollector,
    IFromSource
{
    use TFieldCollector;
    use TTransactionAware;
    use TFromSource;

    protected $type = 'inner';
    protected $parentQuery;
    protected $app;

    /**
     * Init with fields and distinct
     */
    public function __construct(IBuilder $parentQuery, array $fields, string $type='inner')
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
    public function getParentQuery(): IBuilder
    {
        return $this->parentQuery;
    }



    /**
     * Set source and alias
     */
    public function from($source, string $alias=null): IBuilder
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
