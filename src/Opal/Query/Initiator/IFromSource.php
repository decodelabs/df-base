<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query\Initiator;

use Df\Opal\Query\IInitiator;
use Df\Opal\Query\IBuilder;

interface IFromSource extends IInitiator
{
    public function setAliasPrefix(?string $prefix): IFromSource;
    public function getAliasPrefix(): ?string;

    public function from($source, string $alias=null): IBuilder;
    public function fromSelect(string ...$fields): Select;
    public function fromSelectDistinct(string ...$fields): Select;
    //public function fromUnion(): Union;
}
