<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang;

use df;

interface IPipe
{
    public function pipe(callable $callback): IPipe;
    public function pipeEach(array $values, callable $callback): IPipe;
    public function when($truth, callable $yes, callable $no=null): IPipe;
    public function unless($truth, callable $no, callable $yes=null): IPipe;
}
