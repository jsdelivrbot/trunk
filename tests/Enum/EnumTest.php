<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\EnumException;
use PetrKnap\Php\Enum\Test\EnumTest\EnumMock;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    public function goodKeyProvider()
    {
        return [["A", "a"], ["B", "b"]];
    }

    public function wrongKeyProvider()
    {
        return [["C"], ["D"]];
    }

    /**
     * @dataProvider goodKeyProvider
     * @param string $name
     * @param mixed $value
     */
    public function testMagicConstruction_GoodKey($name, $value)
    {
        /** @var EnumMock $enum */
        $enum = EnumMock::$name();

        $this->assertInstanceOf(EnumMock::getClass(), $enum);
        $this->assertSame($name, $enum->getName());
        $this->assertSame($value, $enum->getValue());
    }

    /**
     * @dataProvider wrongKeyProvider
     * @param string $name
     */
    public function testMagicConstruction_WrongKey($name)
    {
        $this->setExpectedException(
            get_class(new EnumException()),
            "",
            EnumException::OUT_OF_RANGE
        );

        EnumMock::$name();
    }

    public function testComparable()
    {
        $this->assertSame(EnumMock::A(), EnumMock::A());
        $this->assertNotSame(EnumMock::A(), EnumMock::B());

        $this->assertTrue(EnumMock::A() == EnumMock::A());
        $this->assertFalse(EnumMock::A() == EnumMock::B());
    }

    /**
     * @dataProvider goodKeyProvider
     * @param string $name
     * @param mixed $value
     */
    public function testGetConstants($name, $value)
    {
        $constants = EnumMock::getMembers();

        $this->assertInternalType("array", $constants);
        $this->assertArrayHasKey($name, $constants);
        $this->assertEquals($value, $constants[$name]);
    }
}
