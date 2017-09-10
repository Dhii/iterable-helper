<?php

namespace Dhii\Iterator;

use Traversable;
use IteratorAggregate;
use Countable;

/**
 * Functionality for counting elements in an iterable.
 *
 * @since [*next-version*]
 */
trait CountIteratorCapableTrait
{
    /**
     * Counts the elements in an iterable.
     *
     * Is optimized to retrieve count from values that support it.
     * - If array, will count in regular way using count();
     * - If {@see Countable}, will do the same;
     * - If {@see IteratorAggregate}, will drill down into internal iterators
     * until the first {@see Countable} is encountered, in which case the same
     * as above will be done.
     * - In any other case, will apply {@see iterator_count()}, which means
     * that it will iterate over the whole traversable to determine the count.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $iterable The iterable to count. Must be finite.
     *
     * @return int The amount of elements.
     */
    protected function _countIterable($iterable)
    {
        $resolve = function ($it) {
            while (!($it instanceof Countable)) {
                if (!($it instanceof IteratorAggregate)) {
                    break;
                }

                $it = $it->getIterator();
            }

            return $it instanceof Countable
                    ? $it
                    : null;
        };

        if (is_array($iterable) || $iterable instanceof Countable) {
            return count($iterable);
        }

        if ($countable = $resolve($iterable)) {
            return count($countable);
        }

        return iterator_count($iterable);
    }
}
