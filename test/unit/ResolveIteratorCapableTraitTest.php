<?php

namespace Dhii\Iterator\UnitTest;

use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;
use Xpmock\TestCase;
use Exception as RootException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use ArrayIterator;
use Dhii\Iterator\ResolveIteratorCapableTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ResolveIteratorCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Iterator\ResolveIteratorCapableTrait';

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
            '__',
            '_createOutOfRangeException',
            '_normalizeInt',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

        $mock->method('__')
            ->will($this->returnCallback(function ($string, $args = []) {
                return vsprintf($string, $args);
            }));
        $mock->method('_createOutOfRangeException')
            ->will($this->returnCallback(function ($message = '', $code = 0, $inner = null, $subject = null) {
                return $this->createOutOfRangeException($message, $code, $inner, $subject);
            }));
        $mock->method('_normalizeInt')
            ->will($this->returnCallback(function ($value) {
                return intval($value);
            }));

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
     * @return MockBuilder The builder for a mock of an object that extends and implements the specified class and interfaces.
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

        return $this->getMockBuilder($paddingClassName);
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
     * Creates a new Out of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return OutOfRangeException The new exception.
     */
    public function createOutOfRangeException($message = '', $code = 0, $inner = null)
    {
        $mock = $this->getMockBuilder('OutOfRangeException')
            ->setConstructorArgs([$message, $code, $inner])
            ->getMock();

        return $mock;
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
     * Creates a new aggregate iterator that returns itself.
     *
     * @since [*next-version*]
     *
     * @return IteratorAggregate The new recursive aggregate iterator.
     */
    public function createIteratorAggregateRecursive()
    {
        $mock = $this->getMockBuilder('IteratorAggregate')
            ->setMethods(['getIterator'])
            ->getMock();

        $mock->method('getIterator')
            ->will($this->returnValue($mock));

        return $mock;
    }

    /**
     * Creates the specified number of aggregate iterators above the inner one.
     *
     * @since [*next-version*]
     *
     * @param Iterator $inner     The bottom-most iterator.
     * @param int      $depth     The number of levels to create.
     * @param array[]  $iterators This will be populated by the list of created iterators,
     *                            in reverse order from outer-most to inner-most.
     *
     * @return IteratorAggregate The top-most iterator.
     */
    public function createNestedIterator(Iterator $inner, $depth = 1, &$iterators = [])
    {
        $iterator = $inner;
        $iterators[] = $iterator;
        for ($i = 0; $i < $depth; ++$i) {
            $iterator = $this->createIteratorAggregate($iterator);
            $iterators[] = $iterator;
        }

        array_reverse($iterators);

        return $iterator;
    }

    /**
     * Creates a new callable object.
     *
     * This object will re-direct calls to `__invoke()` to the supplied callback.
     * This is very useful if the `__invoke()` method needs to be mocked, such as for expectations.
     *
     * @since [*next-version*]
     *
     * @param $callback
     *
     * @return MockObject The new callable object.
     */
    public function createCallable($callback)
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mock->method('__invoke')
            ->will($this->returnCallback(function () use ($callback) {
                $args = func_get_args();

                return call_user_func_array($callback, $args);
            }));

        return $mock;
    }

    /**
     * Creates a new object that is countable, but is not an iterator.
     *
     * @since [*next-version*]
     *
     * @return Countable|IteratorAggregate The new countable.
     */
    public function createCountableNonIterator($elements = [])
    {
        $inner = $this->createIterator($elements);
        $mock = $this->mockClassAndInterfaces('stdClass', ['Iterator'])
                ->setMethods([
//                    'count',
                    'next',
                    'key',
                    'current',
                    'valid',
                    'rewind',
                ])
                ->getMock();

//        $mock->method('count')
//                ->will($this->returnValue(count($elements)));
        $mock->method('next')
            ->will($this->returnCallback(function () use ($inner) { $inner->next(); }));
        $mock->method('key')
            ->will($this->returnCallback(function () use ($inner) { return $inner->key(); }));
        $mock->method('current')
            ->will($this->returnCallback(function () use ($inner) { return $inner->current(); }));
        $mock->method('valid')
            ->will($this->returnCallback(function () use ($inner) { $inner->valid(); }));
        $mock->method('valid')
            ->will($this->returnCallback(function () use ($inner) { $inner->rewind(); }));

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

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that the `_resolveIterator()` method works as expected.
     *
     * @since [*next-version*]
     */
    public function testResolveIterator()
    {
        $testCb = $this->createCallable(function ($it) { return $it instanceof Iterator; });
        $iterator = $this->createIterator();
        $iterators = [];
        $outer = $this->createNestedIterator($iterator, 3, $iterators);
        $limit = 100;
        $expectedCalls = $iterators;
        $expectedCalls[] = $iterator;

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $methodMock = $testCb->expects($this->exactly(5))
            ->method('__invoke');
        call_user_func_array([$methodMock, 'withConsecutive'], $expectedCalls);

        $result = $_subject->_resolveIterator($outer, $testCb, $limit);
        $this->assertSame($iterator, $result, 'Resolution resulted in wrong iterator');
    }

    /**
     * Tests that the `_resolveIterator()` method fails as expected when depth limit is exceeded.
     *
     * @since [*next-version*]
     */
    public function testResolveIteratorFailureLimitExceeded()
    {
        $testCb = $this->createCallable(function ($it) { return $it instanceof Iterator; });
        $iterator = $this->createIterator();
        // Less than the amount of nested iterators
        $limit = 2;
        $outer = $this->createNestedIterator($iterator, $limit + 1);

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $this->setExpectedException('OutOfRangeException');
        $_subject->_resolveIterator($outer, $testCb, $limit);
    }

    /**
     * Tests that the `_resolveIterator()` method fails as expected when encountering an aggregate iterator that aggregates itself.
     *
     * @since [*next-version*]
     */
    public function testResolveIteratorFailureRecursionDetected()
    {
        $testCb = $this->createCallable(function ($it) { return $it instanceof Iterator; });
        // Less than the amount of nested iterators
        $limit = 100;
        $iterator = $this->createIteratorAggregateRecursive();

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $this->setExpectedException('OutOfRangeException');
        $_subject->_resolveIterator($iterator, $testCb, $limit);
    }

    /**
     * Tests that the `_resolveIterator()` method's default parms are the expected ones.
     *
     * @since [*next-version*]
     */
    public function testResolveIteratorDefaultParams()
    {
        $iterator = $this->createIterator();
        $outer = $this->createNestedIterator($iterator, 100);

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_resolveIterator($outer);
        $this->assertSame($iterator, $result, 'Resolution resulted in wrong iterator');
    }

    /**
     * Tests that the `_resolveIterator()` method works as expected when the test does not check for `Iterator`.
     *
     * @since [*next-version*]
     */
    public function testResolveIteratorFailureNonIteratorTest()
    {
        $data = [uniqid('key') => uniqid('val')];
        $testCb = $this->createCallable(function ($it) { return $it instanceof Countable; });
        $iterator = $this->createCountableNonIterator($data);

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $this->setExpectedException('OutOfRangeException');
        $result = $_subject->_resolveIterator($iterator, $testCb);
    }
}
