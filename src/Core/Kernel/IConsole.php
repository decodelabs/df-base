<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Kernel;

use Df;
use Df\Clip\IRequest;

interface IConsole
{
    public function run(): void;
    public function prepareRequest(): IRequest;
    public function handle(IRequest $request): int;
    public function terminate(IRequest $request, int $status=0): void;
}
