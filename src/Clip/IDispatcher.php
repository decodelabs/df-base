<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

use Df\Clip\IRequest;

interface IDispatcher
{
    public function handle(IRequest $request): void;
    public function getStatus(): int;
}
