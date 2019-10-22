<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Opal\Query\Builder;

use Df\Opal\Query\IField;

use DecodeLabs\Glitch;

trait TRelations
{
    /*
    protected function lookupRelationField(string &$fieldName, ?IField $queryField=null)
    {
        Glitch::incomplete();
    }
    */

    /**
     * Get manifest for relation
     */
    protected function lookupRelationManifest(string $fieldName): array
    {
        Glitch::incomplete();
        $sourceManager = $this->getSourceManager();

        return [
            'local' => [
                'source' => $this->getPrimarySource(),
                'field' => 'id'
            ],
            'bridgeLocal' => null,
            'bridgeForeign' => null,
            'foreign' => [
                'source' => $foreignSource,
                'field' => 'jam'
            ]
        ];
    }
}
