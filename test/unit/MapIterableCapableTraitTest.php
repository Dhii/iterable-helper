<?php

namespace Dhii\Iterator\UnitTest;

use ArrayIterator;
use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Dhii\Iterator\MapIterableCapableTrait as TestSubject;
use InvalidArgumentException;
use ReflectionMethod;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class MapIterableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Iterator\MapIterableCapableTrait';

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
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
            ->setMethods($methods)
            ->getMockForTrait();

        $mock->method('__')
                ->will($this->returnArgument(0));

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
     * @param string $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
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
     * @return MockObject|RootException The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Invalid Argument exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return MockObject|InvalidArgumentException The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Invocation exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return MockObject|InvocationExceptionInterface The new exception.
     */
    public function createInvocationException($message = '')
    {
        $mock = $this->mockClassAndInterfaces('Exception', ['Dhii\Invocation\Exception\InvocationExceptionInterface'])
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new `Traversable` mock.
     *
     * @since [*next-version*]
     *
     * @param array $elements The elements for the traversable.
     *
     * @return MockObject|ArrayIterator
     */
    public function mockTraversable($elements = [])
    {
        $mock = $this->getMockBuilder('ArrayIterator')
            ->setConstructorArgs([$elements])
            ->enableProxyingToOriginalMethods()
            ->getMock();

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
     * Tests that `_mapIterable()` works as expected when given an array.
     *
     * @since [*next-version*]
     */
    public function testMapIterableArray()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $data = [
            $key1 => $val1,
            $key2 => $val2,
            $key3 => $val3,
        ];
        $start = 0;
        $count = 0;
        $iterable = $data;
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $cb = function ($value, $key, $iterator) {
            return implode('-', [$key, $value]);
        };
        $expected = [
            $key1 => implode('-', [$key1, $val1]),
            $key2 => implode('-', [$key2, $val2]),
            $key3 => implode('-', [$key3, $val3]),
        ];

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(2))
            ->method('_normalizeInt')
            ->withConsecutive([$start], [$count])
            ->willReturnArgument(0);
        $subject->expects($this->exactly(count($data)))
            ->method('_invokeCallable')
            ->withConsecutive(
                [$cb, [$val1, $key1, $iterable]],
                [$cb, [$val2, $key2, $iterable]],
                [$cb, [$val3, $key3, $iterable]]
            )
            ->will($this->returnCallback(function ($cb, $args) {
                return call_user_func_array($cb, $args);
            }));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
        $this->assertCount(count($data), $result, 'Mapping iterable resulted in a wrong number of items');
        $this->assertArraySubset($expected, $result, 'Mapping iterable resulted in a wrong set');
    }

    /**
     * Tests that `_mapIterable()` works as expected when given non-default limits.
     *
     * @since [*next-version*]
     */
    public function testMapIterableLimits()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $key4 = uniqid('key');
        $val4 = uniqid('val');
        $data = [
            $key1 => $val1,
            $key2 => $val2,
            $key3 => $val3,
            $key4 => $val4,
        ];
        $start = 1;
        $count = 2;
        $iterable = $data;
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $cb = function ($value, $key, $iterator) {
            return implode('-', [$key, $value]);
        };
        $expected = [
            $key2 => implode('-', [$key2, $val2]),
            $key3 => implode('-', [$key3, $val3]),
        ];

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(2))
            ->method('_normalizeInt')
            ->withConsecutive([$start], [$count])
            ->willReturnArgument(0);
        $subject->expects($this->exactly(count($expected)))
            ->method('_invokeCallable')
            ->withConsecutive(
                [$cb, [$val2, $key2, $iterable]],
                [$cb, [$val3, $key3, $iterable]]
            )
            ->will($this->returnCallback(function ($cb, $args) {
                return call_user_func_array($cb, $args);
            }));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
        $this->assertCount(count($expected), $result, 'Mapping iterable resulted in a wrong number of items');
        $this->assertArraySubset($expected, $result, 'Mapping iterable resulted in a wrong set');
    }

    /**
     * Tests that `_mapIterable()` works as expected when given an `stdClass` instance.
     *
     * @since [*next-version*]
     */
    public function testMapIterableStdClass()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $data = [
            $key1 => $val1,
            $key2 => $val2,
            $key3 => $val3,
        ];
        $start = 0;
        $count = 0;
        $iterable = (object) $data;
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $cb = function ($value, $key, $iterable) {
            return implode('-', [$key, $value]);
        };
        $expected = [
            $key1 => implode('-', [$key1, $val1]),
            $key2 => implode('-', [$key2, $val2]),
            $key3 => implode('-', [$key3, $val3]),
        ];

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(2))
            ->method('_normalizeInt')
            ->withConsecutive([$start], [$count])
            ->willReturnArgument(0);
        $subject->expects($this->exactly(count($expected)))
            ->method('_invokeCallable')
            ->withConsecutive(
                [$cb, [$val1, $key1, $iterable]],
                [$cb, [$val2, $key2, $iterable]],
                [$cb, [$val3, $key3, $iterable]]
            )
            ->will($this->returnCallback(function ($cb, $args) {
                return call_user_func_array($cb, $args);
            }));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
        $this->assertCount(count($data), $result, 'Mapping iterable resulted in a wrong number of items');
        $this->assertArraySubset($expected, $result, 'Mapping iterable resulted in a wrong set');
    }

    /**
     * Tests that `_mapIterable()` works as expected when given a `Traversable` instance.
     *
     * @since [*next-version*]
     */
    public function testMapIterableTraversable()
    {
        $key1 = uniqid('key');
        $val1 = uniqid('val');
        $key2 = uniqid('key');
        $val2 = uniqid('val');
        $key3 = uniqid('key');
        $val3 = uniqid('val');
        $data = [
            $key1 => $val1,
            $key2 => $val2,
            $key3 => $val3,
        ];
        $start = 0;
        $count = 0;
        $iterable = $this->mockTraversable($data);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $cb = function ($value, $key, $iterable) {
            return implode('-', [$key, $value]);
        };
        $expected = [
            $key1 => implode('-', [$key1, $val1]),
            $key2 => implode('-', [$key2, $val2]),
            $key3 => implode('-', [$key3, $val3]),
        ];

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(2))
            ->method('_normalizeInt')
            ->withConsecutive([$start], [$count])
            ->willReturnArgument(0);
        $subject->expects($this->exactly(count($expected)))
            ->method('_invokeCallable')
            ->withConsecutive(
                [$cb, [$val1, $key1, $iterable]],
                [$cb, [$val2, $key2, $iterable]],
                [$cb, [$val3, $key3, $iterable]]
            )
            ->will($this->returnCallback(function ($cb, $args) {
                return call_user_func_array($cb, $args);
            }));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
        $this->assertCount(count($data), $result, 'Mapping iterable resulted in a wrong number of items');
        $this->assertArraySubset($expected, $result, 'Mapping iterable resulted in a wrong set');
    }

    /**
     * Tests that `_mapIterable()` fails as expected when given an invalid iterable.
     *
     * @since [*next-version*]
     */
    public function testMapIterableFailureInvalidIterable()
    {
        $start = 0;
        $count = 0;
        $iterable = uniqid('iterable');
        $cb = function () {};
        $exception = $this->createInvalidArgumentException('Invalid iterable');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->throwException($exception));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);

        $this->setExpectedException('InvalidArgumentException');
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
    }

    /**
     * Tests that `_mapIterable()` fails as expected when given an invalid start or end.
     *
     * @since [*next-version*]
     */
    public function testMapIterableFailureInvalidLimits()
    {
        $start = uniqid('start');
        $count = uniqid('count');
        $iterable = [];
        $cb = function () {};
        $exception = $this->createInvalidArgumentException('Invalid limits');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->returnValue($iterable));
        $subject->expects($this->exactly(1))
            ->method('_normalizeInt')
            ->with($start)
            ->will($this->throwException($exception));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);

        $this->setExpectedException('InvalidArgumentException');
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
    }

    /**
     * Tests that `_mapIterable()` fails as expected when given an invalid start or end.
     *
     * @since [*next-version*]
     */
    public function testMapIterableFailureInvocation()
    {
        $start = null;
        $count = null;
        $key = uniqid('key');
        $val = uniqid('val');
        $iterable = [$key => $val];
        $cb = function () {};
        $exception = $this->createInvocationException('Problem during invocation');
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
            ->method('_normalizeIterable')
            ->with($iterable)
            ->will($this->returnValue($iterable));
        $subject->expects($this->exactly(2))
            ->method('_normalizeInt')
            ->withConsecutive([$start], [$count])
            ->will($this->returnArgument(0));
        $subject->expects($this->exactly(1))
            ->method('_invokeCallable')
            ->withConsecutive([$cb, [$val, $key, $iterable]])
            ->will($this->throwException($exception));

        $result = [];
        $method = new ReflectionMethod($subject, '_mapIterable');
        $method->setAccessible(true);

        $this->setExpectedException('Dhii\Invocation\Exception\InvocationExceptionInterface');
        $method->invokeArgs($subject, [$iterable, $cb, $start, $count, &$result]);
    }
}
