<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

interface Source
{
    public function getQuerySourceId(): string;
    public function getDefaultQueryAlias(): string;
}
