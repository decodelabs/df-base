<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Field;

use Df\Opal\Query\Field;
use Df\Opal\Query\Field\Intrinsic;
use Df\Opal\Query\Field\Wildcard;

use Df\Opal\Query\Source\Reference;
use Df\Opal\Query\Source\Manager;

use DecodeLabs\Exceptional;

class Factory
{
    /**
     * Detect field reference from string
     */
    public function fromString(string $field, Reference $source, Manager $sourceManager=null): Field
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

        // Function
        if (preg_match('/^([a-zA-Z0-9_]+) *\((.*)\)$/', $field, $matches)) {
            $func = strtoupper($matches[1]);

            if (in_array($func, Aggregate::FUNCTIONS)) {
                return new Aggregate($source, $func, $matches[2], $alias);
            } else {
                return new Macro($source, $func, $matches[2], $alias);
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

        throw Exceptional::UnexpectedValue(
            'Source alias '.$sourceAlias.' has not been defined'
        );
    }
}
