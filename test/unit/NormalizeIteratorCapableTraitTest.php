<?php

namespace Dhii\Iterator\FuncTest;

use Xpmock\TestCase;
use Dhii\Validation\NormalizeIteratorCapableTrait as TestSubject;
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
     * @return object
     */
    public function createInstance()
    {
        $mock = $this->getMockForTrait(static::TEST_SUBJECT_CLASSNAME);
        $mock->method('_createArrayIterator')->will($this->returnCallback(function ($array) {
            return new ArrayIterator($array);
        }));
        $mock->method('_createTraversableIterator')->will($this->returnCallback(function ($traversable) {
            return new IteratorIterator($traversable);
        }));

        return $mock;
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
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        );

        $result = $_subject->_normalizeIterator($data);
        $this->assertInstanceOf('Iterator', $result, 'The result type is wrong');
        $_result = iterator_to_array($result);
        $this->assertEquals($data, $_result, 'The result state is wrong', 0.0, 10, true);
    }

    /*
     * Tests whether iterator to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorIterator()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        );

        $result = $_subject->_normalizeIterator(new ArrayIterator($data));
        $this->assertInstanceOf('Iterator', $result, 'The result type is wrong');
        $_result = iterator_to_array($result);
        $this->assertEquals($data, $_result, 'The result state is wrong', 0.0, 10, true);
    }

    /*
     * Tests whether iterator aggregate to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorAggregate()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = array(
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        );

        $result = $_subject->_normalizeIterator($this->createIteratorAggregate($data));
        $this->assertInstanceOf('Iterator', $result, 'The result type is wrong');
        $_result = iterator_to_array($result);
        $this->assertEquals($data, $_result, 'The result state is wrong', 0.0, 10, true);
    }

    /*
     * Tests whether non-iterable value to iterator normalization works as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeIteratorOther()
    {
        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);
        $data = uniqid('value-');

        $result = $_subject->_normalizeIterator($data);
        $this->assertInstanceOf('Iterator', $result, 'The result type is wrong');
        $_result = iterator_to_array($result);
        $this->assertEquals([$data], $_result, 'The result state is wrong', 0.0, 10, true);
    }
}
