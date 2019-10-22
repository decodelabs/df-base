<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df;
use Df\Opal\Query\IBuilder;
use Df\Opal\Query\IInitiator;
use Df\Opal\Query\Source\Derived;

use DecodeLabs\Glitch;

trait TDerivable
{
    protected $derivationParent;

    /**
     * Initiate derivation
     */
    public function setDerivationParent(?IInitiator $parent): IDerivable
    {
        $this->derivationParent = $parent;
        return $this;
    }

    /**
     * Get derivation parent
     */
    public function getDerivationParent(): ?IInitiator
    {
        return $this->derivationParent;
    }

    /**
     * Complete derivation source, continue with main query
     */
    public function endDerivation(string $alias=null): IBuilder
    {
        if (!$this->derivationParent) {
            throw Glitch::ELogic(
                'Cannot create derived source - no parent query available'
            );
        }

        if ($alias === null) {
            $alias = uniqid('drv_');
        }

        $adapter = new Derived($this, $alias);
        $output = $this->derivationParent->from($adapter, $alias);
        $this->asSubQuery($output, 'derivation');

        $this->derivationParent = null;
        return $output;
    }
}
