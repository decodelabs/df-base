<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace df\lang\debug\dumper;

use df;
use df\lang;

use Symfony\Component\VarDumper\Dumper\HtmlDumper as SymfonyHtmlDumper;

class HtmlDumper extends SymfonyHtmlDumper
{
    protected $styles = [
        'default' => 'background-color:#fff; color:#888; line-height:1.2; font-weight:normal; font:12px Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:100000',
        'num' => 'color:#a814e3',
        'const' => 'color:#b50acc',
        'str' => 'color:#cc2123',
        'cchr' => 'color:#222',
        'note' => 'color:#0cb300',
        'ref' => 'color:#a0a0a0',
        'public' => 'color:#795da3',
        'protected' => 'color:#795da3',
        'private' => 'color:#795da3',
        'meta' => 'color:#0cb300',
        'key' => 'color:#df5000',
        'index' => 'color:#a71d5d',
    ];
}
