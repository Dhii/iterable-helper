<?php

namespace Dhii\Iterator\FuncTest;

use InvalidArgumentException;
use Traversable;
use Xpmock\TestCase;
use Dhii\Iterator\NormalizeIteratorCapableTrait as TestSubject;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use ArrayIterator;
use IteratorIterator;
use IteratorAggregate;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeIteratorCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Iterator\NormalizeIteratorCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject
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
     * @param array  $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The object that extends and implements the specified class and interfaces.
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
     * Creates a new iterator aggregate.
     *
     * @since [*next-version*]
     *
     * @return IteratorAggregate $array The new iterator aggregate.
     */
    public function createIteratorAggregate($array)
    {
        $mock = $this->mock('IteratorAggregate')
                ->getIterator(new ArrayIterator($array))
                ->new();

        return $mock;
    }

    /**
     * Creates a new Invalid Argument exception.
     *
     * @param string $message The error message.
     *
     * @return MockObject|InvalidArgumentException
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new iterator that will iterate over an array.
     *
     * @since [*next-version*]
     *
     * @param array $elements The elements for the iterator.
     *
     * @return MockObject|ArrayIterator The new iterator.
     */
    public function createArrayIterator($elements = [])
    {
        $mock = $this->getMockBuilder('ArrayIterator')
            ->setConstructorArgs([$elements])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new iterator that will iterate over a traversable.
     *
     * @since [*next-version*]
     *
     * @param Traversable $traversable The traversable for the iterator.
     *
     * @return MockObject|ArrayIterator The new iterator.
     */
    public function createTraversableIterator(Traversable $traversable)
    {
        $mock = $this->getMockBuilder('IteratorIterator')
            ->setConstructorArgs([$traversable])
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

        $this->assertInternalType('object', $subject, 'A valid instance of the test subject could not be created');
    }

    /*
     * Tests whether array to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorArray()
    {
        $data = array(
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        );
        $iterator = $this->createArrayIterator($data);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
                ->method('_createArrayIterator')
                ->with($data)
                ->will($this->returnValue($iterator));

        $result = $_subject->_normalizeIterator($data);
        $this->assertEquals($iterator, $result, 'The result state is wrong', 0.0, 10, true);
    }

    /*
     * Tests whether iterator to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorIterator()
    {
        $data = array(
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        );
        $iterator = $this->createArrayIterator($data);
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_normalizeIterator($iterator);
        $this->assertEquals($iterator, $result, 'The result state is wrong', 0.0, 10, true);
    }

    /*
     * Tests whether iterator aggregate to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorAggregate()
    {
        $data = array(
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        );
        $traversable = $this->createIteratorAggregate($data);
        $iterator = $this->createTraversableIterator($traversable);
        $subject = $this->createInstance(['_createTraversableIterator']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
                ->method('_createTraversableIterator')
                ->with($traversable)
                ->will($this->returnValue($iterator));

        $result = $_subject->_normalizeIterator($traversable);
        $this->assertEquals($iterator, $result, 'The result state is wrong', 0.0, 10, true);
    }

    /*
     * Tests whether `stdClass` to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorStdClass()
    {
        $data = [uniqid('key') => uniqid('val')];
        $iterator = $this->createArrayIterator($data);
        $list = (object) $data;
        $subject = $this->createInstance(['_createArrayIterator']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
                ->method('_createArrayIterator')
                ->with($data)
                ->will($this->returnValue($iterator));

        $result = $_subject->_normalizeIterator($list);
        $this->assertEquals($iterator, $result, 'The result state is wrong', 0.0, 10, true);
    }

    /**
     * Tests that the `_resolveIterator()` method fails as expected when given a value that is not iterable..
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorFailureInvalidIterable()
    {
        $iterator = uniqid('non-iterable');
        $exception = $this->createInvalidArgumentException('Not a valid iterable');
        $subject = $this->createInstance(['_createInvalidArgumentException']);
        $_subject = $this->reflect($subject);

        $subject->expects($this->exactly(1))
                ->method('_createInvalidArgumentException')
                ->with(
                    $this->isType('string'),
                    null,
                    null,
                    $iterator
                )
                ->will($this->returnValue($exception));

        $this->setExpectedException('InvalidArgumentException');
        $result = $_subject->_normalizeIterator($iterator);
    }
}
