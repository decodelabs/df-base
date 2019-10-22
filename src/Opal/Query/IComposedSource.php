<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

interface IComposedSource extends ISource
{
    public function getFieldNames(): array;
}
