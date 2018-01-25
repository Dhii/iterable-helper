<?php

namespace Dhii\Iterator;

use Traversable;
use Iterator;

/**
 * Functionality for normalizing iterators.
 *
 * @since [*next-version*]
 */
trait NormalizeIteratorCapableTrait
{
    /**
     * Normalizes a value into an iterator.
     *
     * If the value is iterable, the resulting iterator would iterate over the
     * elements in the iterable.
     * Otherwise, such as if the value is scalable, or an object of a different
     * type, it will be treated as an iterable with one element: itself.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|mixed $value The value to normalize.
     *
     * @return Iterator The normalized iterator.
     */
    protected function _normalizeIterator($value)
    {
        if (!is_array($value) && !($value instanceof Traversable)) {
            $value = [$value];
        }

        if ($value instanceof Iterator) {
            return $value;
        }

        if (is_array($value)) {
            return $this->_createArrayIterator($value);
        }

        // If not array them gotta be traversable
        return $this->_createTraversableIterator($value);
    }

    /**
     * Creates an iterator that will iterate over the given array.
     *
     * @param array $array The array to create an iterator for.
     *
     * @since [*next-version*]
     *
     * @return Iterator The iterator that will iterate over the array.
     */
    abstract protected function _createArrayIterator(array $array);

    /**
     * Creates an iterator that will iterate over the given traversable.
     *
     * @param Traversable $traversable The traversable to create an iterator for.
     *
     * @since [*next-version*]
     *
     * @return Iterator The iterator that will iterate over the traversable.
     */
    abstract protected function _createTraversableIterator(Traversable $traversable);
}
