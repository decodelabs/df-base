<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Core\Kernel;

use DecodeLabs\Terminus\Command\Request;

interface Console
{
    public function run(): void;
    public function prepareRequest(): Request;
    public function handle(Request $request): int;
    public function terminate(Request $request, int $status=0): void;
}
