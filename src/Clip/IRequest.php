<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Clip;

use Df;

interface IRequest
{
    public function setScript(string $script): IRequest;
    public function getScript(): ?string;
    public function getScriptPath(): ?string;
    public function withScript(string $script): IRequest;

    public function setPath(string $path): IRequest;
    public function getPath(): ?string;
    public function withPath(string $path): IRequest;

    public function setCommandParams(array $params): IRequest;
    public function getCommandParams(): array;
    public function getCommandParam(string $key): ?string;
    public function hasCommandParam(string $key): bool;
    public function withCommandParams(array $params): IRequest;

    public function getServerParams(): array;
    public function getServerParam(string $key): ?string;
    public function hasServerParam(string $key): bool;
}
