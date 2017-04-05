<?php
namespace Test\PhpFlo\Builder;

use PhpFlo\Builder\ComponentFactory;
use PhpFlo\Exception\InvalidDefinitionException;

class ComponentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $diFactory = new ComponentFactory();

        $this->assertInstanceOf(ComponentFactory::class, $diFactory);
    }

    public function testClassNotFoundException()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Component class PhpFlo\Component\SomeVirtualComponent not found');

        $diFactory = new ComponentFactory();
        $diFactory->build('SomeVirtualComponent');
    }

    public function testInvalidComponentException()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Component stdClass doesn\'t appear to be a valid PhpFlo component');

        $diFactory = new ComponentFactory();
        $diFactory->build(\stdClass::class);
    }
}
