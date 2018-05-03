<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df;

use df;
use df\lang\stack\Frame;
use df\lang\stack\Trace;

interface IError
{
    public function setData($data);
    public function getData();

    public function setHttpCode(?int $code);
    public function getHttpCode(): ?int;

    public function getStackFrame(): Frame;
    public function getStackTrace(): Trace;
}
