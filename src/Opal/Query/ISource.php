<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Opal\Query;

use Df;

interface ISource
{
    public function getQuerySourceId(): string;
    public function getDefaultQueryAlias(): string;
}
