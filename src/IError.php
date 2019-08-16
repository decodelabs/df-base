<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df;

use Df;
use Glitch\Stack\Frame;
use Glitch\Stack\Trace;

interface IError
{
    public function setData($data);
    public function getData();

    public function setHttpCode(?int $code);
    public function getHttpCode(): ?int;

    public function getStackFrame(): Frame;
    public function getStackTrace(): Trace;
}
