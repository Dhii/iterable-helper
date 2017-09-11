<?php

namespace Dhii\Iterator\FuncTest;

use Xpmock\TestCase;
use ArrayIterator;
use IteratorIterator;
use InfiniteIterator;
use IteratorAggregate;
use Dhii\Iterator\CountIterableCapableTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CountIterableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Iterator\CountIterableCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return object
     */
    public function createInstance()
    {
        $mock = $this->getMockForTrait(static::TEST_SUBJECT_CLASSNAME);

        return $mock;
    }

    /**
     * Creates a new iterator aggregate, the internal iterator of which can be counted.
     *
     * @since [*next-version*]
     *
     * @param array $array The array with elements which the iteraetor will iterate over.
     *
     * @return IteratorAggregate $array The new iterator aggregate.
     */
    public function createIteratorAggregateCountable(array $array)
    {
        $me = $this;
        // This creates a double-layer iterator aggregate
        $mock = $this->mock('IteratorAggregate')
                ->getIterator(function () use (&$me, $array) {
                    $it = $me->mock('IteratorAggregate')
                            ->getIterator(function () use ($array) {
                                return new ArrayIterator($array);
                            })
                            ->new();

                    return $it;
                })
                ->new();

        return $mock;
    }

    /**
     * Creates an iterator aggregate that contains a non-countable iterator at some level inside it.
     *
     * @since [*next-version*]
     *
     * @param array $array The array with elements which the iteraetor will iterate over.
     *
     * @return IteratorAggregate The new iterator.
     */
    public function createIteratorAggregateNonCountable(array $array)
    {
        $me = $this;
        // This creates a double-layer iterator aggregate
        $mock = $this->mock('IteratorAggregate')
                ->getIterator(function () use (&$me, $array) {
                    $it = $me->mock('IteratorAggregate')
                            ->getIterator(function () use ($array) {
                                return new IteratorIterator(new ArrayIterator($array));
                            })
                            ->new();

                    return $it;
                })
                ->new();

        return $mock;
    }

    /**
     * Creates something which can be iterated over infinitely.
     *
     * @since [*next-version*]
     *
     * @param array|null $array The array, over which to iterate infinitely.
     *                          If null, an array with one element that is a random string will be used.
     *
     * @return Traversable The infinite iterator.
     */
    public function createIteratorInfinite($array = null)
    {
        if (is_null($array)) {
            $array = [uniqid()];
        }

        return new InfiniteIterator(new ArrayIterator($array));
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType('object', $subject, 'A valid instance of the test subject could not be created');
    }

    /**
     * Test that counting an array works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableArray()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );

        $result = $_subject->_countIterable($data);
        $this->assertSame(count($data), $result, 'Wrong result when counting an array');
    }

    /**
     * Tests that counting a countable works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableCountable()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );

        $result = $_subject->_countIterable(new ArrayIterator($data));
        $this->assertSame(count($data), $result, 'Wrong result when counting a countable');
    }

    /**
     * Tests that counting an iterator aggregate containing a countable works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableAggregateCountable()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );

        $result = $_subject->_countIterable($this->createIteratorAggregateCountable($data));
        $this->assertSame(count($data), $result, 'Wrong result when counting a countable');
    }

    /**
     * Tests that counting a non-countable works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableAggregateNonCountable()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );

        $result = $_subject->_countIterable($this->createIteratorAggregateNonCountable($data));
        $this->assertSame(count($data), $result, 'Wrong result when counting a countable');
    }

    /**
     * Tests that counting an infinite works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableInfinite()
    {
        $this->markTestSkipped('Just illustrates how returning an infinite list of errors causes infinite loop');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );

        $result = $_subject->_countIterable($this->createIteratorInfinite($data));
        $this->assertSame(count($data), $result, 'Wrong result when counting a countable');
    }
}
