<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

use Df\Plug\IContext as IPlugContext;
use Df\Clip\IInput;

use Psr\Log\LoggerInterface;

interface IContext extends IPlugContext, LoggerInterface
{
    public function render($output, ?string $modifier=null): void;

    // Formatters
    public function clearScreen(): IContext;
    public function tab(int $count=1): IContext;

    // Input
    public function ask(string $message, string $default=null): IInput;
    public function askPassword(string $message): IInput;
    public function confirm(string $message, bool $default=null): IInput;
}
