<?php
namespace Test\PhpFlo\Builder;

use PhpFlo\Builder\ComponentFactory;

class ComponentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $diFactory = new ComponentFactory();

        $this->assertInstanceOf(ComponentFactory::class, $diFactory);
    }

    /**
     * @expectedException \PhpFlo\Exception\InvalidDefinitionException
     * @expectedExceptionMessage Component class PhpFlo\Component\SomeVirtualComponent not found
     */
    public function testClassNotFoundException()
    {
        $diFactory = new ComponentFactory();
        $diFactory->build('SomeVirtualComponent');
    }

    /**
     * @expectedException \PhpFlo\Exception\InvalidDefinitionException
     */
    public function testInvalidComponentException()
    {
        $diFactory = new ComponentFactory();
        $diFactory->build(\stdClass::class);
    }
}
