<?php

namespace Vulcan\Collections\Support;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Vulcan\Collections\Contracts\Collection;

class Arr
{
    /**
     * Flatten the passed multi-dimensional array into a single level.
     *
     * @param array $array
     *
     * @return array
     */
    public static function flatten($array)
    {
        $array = $array instanceof Collection ? $array->all() : $array;

        return iterator_to_array(
            new RecursiveIteratorIterator(new RecursiveArrayIterator($array)),
            false
        );
    }

    /**
     * Get an item from an array with dot notation support.
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(array $array, $key = null, $default = null)
    {
        if (is_null($key)) {
            return;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $token) {
            if (! is_array($array) or ! array_key_exists($token, $array)) {
                return $default;
            }

            $array = $array[$token];
        }

        return $array;
    }

    /**
     * Transmute passed items as an array.
     *
     * @param mixed $items
     *
     * @return array
     */
    public static function transmute($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Collection) {
            return $items->all();
        }

        return (array) $items;
    }
}
