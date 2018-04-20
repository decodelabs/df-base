<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\error;

use df;
use df\lang;

interface IError
{
    public function setData($data);
    public function getData();

    public function setHttpCode(?int $code);
    public function getHttpCode(): ?int;

    public function getStackCall(): lang\debug\StackCall;
    public function getStackTrace(): lang\debug\StackTrace;
}
