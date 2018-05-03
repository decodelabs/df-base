<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
namespace Df\Link;

use Df;
use Df\Data\Tree;

use Psr\Http\Message\UriInterface;

interface IUri extends UriInterface
{
    public function setScheme(?string $scheme): UriInterface;

    public function setUsername(?string $username): UriInterface;
    public function getUsername(): ?string;
    public function setPassword(?string $password): UriInterface;
    public function getPassword(): ?string;
    public function setUserInfo(?string $username, ?string $password=null): UriInterface;

    public function setHost(?string $host): UriInterface;
    public function setPort(?int $port): UriInterface;

    public function setPath(?string $path): UriInterface;

    public function setQuery(?string $query): UriInterface;
    public function setQueryTree(?Tree $tree): UriInterface;
    public function getQueryTree(): Tree;

    public function setFragment(?string $fragment): UriInterface;
}
