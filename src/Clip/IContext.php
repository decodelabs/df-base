<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;
use Df\Plug\IContext as IPlugContext;
use Psr\Log\LoggerInterface;

interface IContext extends IPlugContext, LoggerInterface
{
    public function render($output, ?string $modifier=null): void;
}
