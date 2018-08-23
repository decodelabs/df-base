<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df;

use Df\Opal\Query\IField;
use Df\Opal\Query\Field\Intrinsic;
use Df\Opal\Query\Field\Wildcard;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Source\Manager;

class Factory
{
    /**
     * Detect field reference from string
     */
    public function fromString(string $field, Reference $source, Manager $sourceManager=null): IField
    {
        if (preg_match('/(.+) +as +([^ ]+)$/', $field, $matches)) {
            $field = $matches[1];
            $alias = $matches[2];
        } else {
            $alias = null;
        }

        // Intrinsic
        if (preg_match('/^(([^()].+)\.)?([^()]+)$/', $field, $matches)) {
            $source = $this->normalizeSource($matches[2], $source, $sourceManager);
            $name = $matches[3];

            if ($name === '*') {
                return new Wildcard($source);
            } else {
                return new Intrinsic($source, $name, $alias);
            }
        }


        dd($field, $alias);
    }

    /**
     * Normalize source from current and manager
     */
    protected function normalizeSource(?string $sourceAlias, Reference $source, ?Manager $sourceManager=null): Reference
    {
        if ($sourceAlias === null
        || $sourceAlias === ''
        || $sourceAlias === $source->getAlias()) {
            return $source;
        }

        if ($sourceManager && ($ref = $sourceManager->getReference($sourceAlias))) {
            return $ref;
        }

        throw Df\Error::EUnexpectedValue('Source alias '.$sourceAlias.' has not been defined');
    }
}
