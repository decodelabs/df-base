<?php
/**
 * This file is part of the Decode Framework
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace Df\Core\Event;

use Df;

class Dispatcher
{
    protected $events = [];

    /**
     * Register an event for before trigger
     */
    public function before(string $id, callable $callback): Dispatcher
    {
        $this->events['<'.$id][spl_object_id($callback)] = $callback;
        return $this;
    }

    /**
     * Register an event for after trigger
     */
    public function after(string $id, callable $callback): Dispatcher
    {
        $this->events['>'.$id][spl_object_id($callback)] = $callback;
        return $this;
    }

    /**
     * Trigger before handlers
     */
    public function triggerBefore(string $id, ...$args): Dispatcher
    {
        foreach ($this->events['<'.$id] ?? [] as $callback) {
            $callback(...$args);
        }

        return $this;
    }

    /**
     * Trigger after handlers
     */
    public function triggerAfter(string $id, ...$args): Dispatcher
    {
        foreach ($this->events['>'.$id] ?? [] as $callback) {
            $callback(...$args);
        }

        return $this;
    }


    /**
     * Is this before event registered?
     */
    public function hasBefore(string ...$ids): bool
    {
        foreach ($ids as $id) {
            if (isset($this->events['<'.$id])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is this before event registered?
     */
    public function hasAfter(string ...$ids): bool
    {
        foreach ($ids as $id) {
            if (isset($this->events['>'.$id])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is this before event registered?
     */
    public function has(string ...$ids): bool
    {
        foreach ($ids as $id) {
            if (isset($this->events['>'.$id]) || isset($this->events['<'.$id])) {
                return true;
            }
        }

        return false;
    }



    /**
     * Check ids and run callback
     */
    public function withBefore(array $ids, callable $callback): Dispatcher
    {
        if ($this->hasBefore(...$ids)) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Check ids and run callback
     */
    public function withAfter(array $ids, callable $callback): Dispatcher
    {
        if ($this->hasAfter(...$ids)) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Check ids and run callback
     */
    public function with(array $ids, callable $callback): Dispatcher
    {
        if ($this->has(...$ids)) {
            $callback($this);
        }

        return $this;
    }



    /**
     * Remove before handler(s)
     */
    public function removeBefore(string $id, callable $callback=null): Dispatcher
    {
        if ($callback) {
            unset($this->events['<'.$id][spl_object_id($callback)]);
        } else {
            unset($this->events['<'.$id]);
        }

        return $this;
    }

    /**
     * Remove after handler(s)
     */
    public function removeAfter(string $id, callable $callback=null): Dispatcher
    {
        if ($callback) {
            unset($this->events['>'.$id][spl_object_id($callback)]);
        } else {
            unset($this->events['>'.$id]);
        }

        return $this;
    }

    /**
     * Remove before and after handler(s)
     */
    public function remove(string $id, callable $callback=null): Dispatcher
    {
        $this->removeBefore($id, $callback);
        $this->removeAfter($id, $callback);

        return $this;
    }

    /**
     * Clear all events
     */
    public function clear(): Dispatcher
    {
        $this->events = [];
        return $this;
    }
}
