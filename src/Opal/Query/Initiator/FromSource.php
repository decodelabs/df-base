<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Initiator;

use Df\Opal\Query\Initiator;
use Df\Opal\Query\Builder;

interface FromSource extends Initiator
{
    public function setAliasPrefix(?string $prefix): FromSource;
    public function getAliasPrefix(): ?string;

    public function from($source, string $alias=null): Builder;
    public function fromSelect(string ...$fields): Select;
    public function fromSelectDistinct(string ...$fields): Select;
    //public function fromUnion(): Union;
}
