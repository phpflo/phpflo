<?php
namespace Test\PhpFlo\Core\Builder;

use PhpFlo\Core\Builder\ComponentFactory;
use PhpFlo\Common\Exception\InvalidDefinitionException;
use PhpFlo\Core\Test\TestCase;

class ComponentFactoryTest extends TestCase
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
