<?php
namespace Test\PhpFlo\Builder;

use PhpFlo\Builder\ComponentFactory;

class ComponentFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $diFactory = new ComponentFactory();

        $this->assertInstanceOf('PhpFlo\Builder\ComponentFactory', $diFactory);
    }

    /**
     * @expectedException \PhpFlo\Exception\InvalidDefinitionException
     * @expectedExceptionMessage Component class PhpFlo\Component\SomeVirtualComponent not found
     */
    public function testClassNotFoundException()
    {
        $diFactory = new ComponentFactory();
        $component = $diFactory->build('SomeVirtualComponent');
    }

    /**
     * @expectedException \PhpFlo\Exception\InvalidDefinitionException
     */
    public function testInvalidComponentException()
    {
        $this->markTestIncomplete('If someone finds an easy way to mock non-existing classes in autoload: Give it a try');
    }
}
