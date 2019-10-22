<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Plug;

trait TContextProxy
{
    protected $context;

    /**
     * Init with context
     */
    public function __construct(IContext $context)
    {
        $this->context = $context;
    }

    /**
     * Pass member access to context
     */
    public function __get(string $key)
    {
        return $this->context->{$key};
    }

    /**
     * Pass calls to context
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->context, $method], $args);
    }
}
