<?php

namespace Vulcan\Collections;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Vulcan\Collections\Contracts\Collection as CollectionContract;
use Vulcan\Collections\Support\Arr;

class Collection implements CollectionContract, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Items of the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection instance.
     *
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = Arr::transmute($items);
    }

    /**
     * Helper static method to easily create a new Collection instance.
     *
     * @param mixed $items;
     *
     * @return self
     */
    public static function make($items)
    {
        return new static($items);
    }

    /**
     * Returns an external iterator.
     *
     * @return void
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Retrieve all the items from the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get the specified item from the collection.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Arr::get($this->items, $key);
    }

    /**
     * Push an item to the collection.
     *
     * @param mixed $value
     */
    public function push($value)
    {
        return $this->itemSet($value);
    }

    /**
     * Put the specified item in the collection with the given key.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function put($key, $value)
    {
        return $this->itemSet($value, $key);
    }

    /**
     * Remove the given item from the collection.
     *
     * @param mixed $key
     */
    public function remove($key)
    {
        return $this->itemUnset($key);
    }

    /**
     * Determine if the given item exists in the collection.
     *
     * @param mixed $key
     */
    public function exists($key)
    {
        return $this->itemExists($key);
    }

    /**
     * Return the number of items in the collection.
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Return the first item from the collection.
     *
     * @return mixed
     */
    public function first()
    {
        return $this->itemFirst();
    }

    /**
     * Return the last item from the collection.
     *
     * @return mixed
     */
    public function last()
    {
        return $this->itemLast();
    }

    /**
     * Loop through the items with the defined callback.
     *
     * @param callable $callable
     *
     * @return void
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Apply the callable to the collection items.
     *
     * @param callable $callable
     *
     * @return self
     */
    public function map(callable $callable)
    {
        $keys = array_keys($this->items);
        $results = array_map($callable, $this->items, $keys);

        return new self($results);
    }

    /**
     * Filter the collection items through the callable.
     *
     * @param callable $callable
     *
     * @return self
     */
    public function filter(callable $callable)
    {
        $results = [];

        foreach ($this->items as $key => $item) {
            if ($callable($item, $key)) {
                $results[] = $item;
            }
        }

        return new self($results);
    }

    /**
     * Reject the collection items through the callable.
     *
     * @param callable $callable
     *
     * @return self
     */
    public function reject(callable $callable)
    {
        return $this->filter(function ($item, $key) use ($callable) {
            return !$callable($item, $key);
        });
    }

    //

    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * Returns a new Collection instance containing an
     * indexed array of values.
     *
     * @return self
     */
    public function values()
    {
        return new static(array_values($this->items));
    }

    /**
     * Returns a new Collection instance containing an
     * indexed array of keys.
     *
     * @return self
     */
    public function keys()
    {
        return new static(array_keys($this->items));
    }

    /**
     * Returns a new Collection instance containing a
     * flattened array of items.
     *
     * @return self
     */
    public function flatten()
    {
        return new static(Arr::flatten($this->items));
    }

    /**
     * Sort the collection of item values through a user-defined
     * comparison function.
     *
     * @param callable|null $callable
     *
     * @return static
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;

        $callback
            ? uasort($items, $callback)
            : asort($items);

        return new static($items);
    }

    /**
     * Sort the collection of item keys through a user-defined
     * comparison function.
     *
     * @param callable|null $callable
     *
     * @return static
     */
    public function sortByKey(callable $callback = null)
    {
        $items = $this->items;

        $callback
            ? uksort($items, $callback)
            : ksort($items);

        return new static($items);
    }

    /**
     * Reverse the collection items.
     *
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param callable $callback
     * @param bool     $preserveKeys
     *
     * @return static
     */
    public function groupBy(callable $callback)
    {
        $results = [];

        foreach ($this->items as $key => $value) {
            $groupKey = $callback($value, $key, $this->items);

            if (!isset($results[$groupKey])) {
                $results[$groupKey] = [];
            }

            $results[$groupKey][$key] = $value;
        }

        return new static($results);
    }

    //

    /**
     * Set the given array value with the provided key or index.
     *
     * @param mixed $value
     * @param mixed $key
     */
    private function itemSet($value, $key = null)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the given key or index from the array.
     *
     * @param mixed $key
     *
     * @return void
     */
    private function itemUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Checks if the given key or index exists in the array.
     *
     * @param mixed $key
     *
     * @return bool
     */
    private function itemExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Set the internal pointer of an array to its first element
     * and return its value.
     *
     * @return mixed
     */
    private function itemFirst()
    {
        $items = $this->items;

        return reset($items);
    }

    /**
     * Set the internal pointer of an array to its last element
     * and return its value.
     *
     * @return mixed
     */
    private function itemLast()
    {
        $items = $this->items;

        return end($items);
    }
}
