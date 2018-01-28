<?php

namespace Dhii\Iterator\FuncTest;

use Iterator;
use Traversable;
use Xpmock\TestCase;
use ArrayIterator;
use InfiniteIterator;
use IteratorAggregate;
use Exception as RootException;
use Dhii\Iterator\CountIterableCapableTrait as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues($methods, [

        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string $className      Name of the class for the mock to extend.
     * @param array  $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return object The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Create a new non-countable iterator.
     *
     * @param array $elements The elements to iterate over.
     *
     * @return Iterator The new iterator.
     */
    public function createIteratorNotCountable(array $elements = [])
    {
        $inner = $this->createIterator($elements);
//        $iterator = $this->getMockBuilder('NoRewindIterator')
//                ->setConstructorArgs([$inner])
//                ->getMock();
        $iterator = new \CallbackFilterIterator($inner, function ($current, $key, $iterator) { return true; });

        $this->assertNotInstanceOf('Countable', $iterator, 'Iterator is countable');

        return $iterator;
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
     * Creates a new iterator.
     *
     * @since [*next-version*]
     *
     * @param array $elements The elements that the iterator will iterate over.
     *
     * @return Iterator The new iterator.
     */
    public function createIterator(array $elements = [])
    {
        return new ArrayIterator($elements);
    }

    /**
     * Creates a new aggregate iterator.
     *
     * @since [*next-version*]
     *
     * @param Traversable $iterator The iterator that the result will aggregate.
     *
     * @return IteratorAggregate The new aggregate iterator.
     */
    public function createIteratorAggregate(Traversable $iterator)
    {
        $mock = $this->getMockBuilder('IteratorAggregate')
            ->setMethods(['getIterator'])
            ->getMock();

        $mock->method('getIterator')
            ->will($this->returnValue($iterator));

        return $mock;
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
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );
        $inner = $this->createIterator($data);
        $iterator = $this->createIteratorAggregate($inner);
        $subject = $this->createInstance(['_resolveIterator']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_resolveIterator')
            ->with(
                $iterator,
                $this->isType('callable')
            )
            ->will($this->returnCallback(function ($it, $test) use ($inner) {
                $test($it);

                return $inner;
            }));

        $result = $_subject->_countIterable($iterator);
        $this->assertSame(count($data), $result, 'Wrong result when counting a countable');
    }

    /**
     * Tests that counting a countable works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableCountableUnresolvable()
    {
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );
        $inner = $this->createIterator($data);
        $iterator = $this->createIteratorAggregate($inner);
        $subject = $this->createInstance(['_resolveIterator']);
        $_subject = $this->reflect($subject);
        $exception = $this->createException(uniqid('Cannot resolve '));

        $subject->expects($this->exactly(1))
            ->method('_resolveIterator')
            ->with(
                $iterator,
                $this->isType('callable')
            )
            ->will($this->throwException($exception));

        $result = $_subject->_countIterable($iterator);
        $this->assertSame(count($data), $result, 'Wrong result when counting a countable');
    }

    /**
     * Tests that counting a countable works as expected.
     *
     * @since [*next-version*]
     */
    public function testCountIterableNotCountable()
    {
        $data = array(
            uniqid('value-'),
            uniqid('value-'),
            uniqid('value-'),
        );
        $iterator = $this->createIteratorNotCountable($data);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_countIterable($iterator);

        $this->assertSame(count($data), $result, 'Wrong result when counting a non-countable');
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
