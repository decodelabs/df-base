<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df\Plug\IContext as IPlugContext;
use Df\Clip\IInput;

use Psr\Log\LoggerInterface;

use Df\Clip\Input\Question;
use Df\Clip\Input\Password;
use Df\Clip\Input\Confirmation;

use Df\Clip\Widget\Spinner;

interface IContext extends IPlugContext, LoggerInterface
{
    public function render($output, ?string $modifier=null): void;

    // Formatters
    public function clearScreen(): IContext;
    public function tab(int $count=1): IContext;

    // Input
    public function ask(string $message, string $default=null): Question;
    public function askPassword(string $message): Password;
    public function confirm(string $message, bool $default=null): Confirmation;

    // Widgets
    public function spinner(string $style=null): Spinner;
}
