<?php
namespace Test\PhpFlo\Builder;

use PhpFlo\Builder\ComponentDiFinder;

class ComponentDiFinderTest extends \PHPUnit_Framework_TestCase
{

    public function testInstance()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub('\Symfony\Component\DependencyInjection\ContainerInterface')
        );

        $this->assertInstanceOf('PhpFlo\Builder\ComponentDiFinder', $diFinder);
    }

    public function testFindComponentInDi()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(
                '\Symfony\Component\DependencyInjection\ContainerInterface',
                [
                    'get' => $this->stub('PhpFlo\Common\ComponentInterface', [], 'SomeComponent'),
                ]
            )
        );

        $component = $diFinder->build('SomeComponent');
        $this->assertInstanceOf('SomeComponent', $component);
    }

    /**
     * @expectedException \PhpFlo\Exception\InvalidDefinitionException
     * @expectedExceptionMessage Component SomeInvalidComponent doesn't appear to be a valid PhpFlo component
     */
    public function testInvalidComponent()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(
                '\Symfony\Component\DependencyInjection\ContainerInterface',
                [
                    'get' => $this->stub('SomeInvalidComponent'),
                ]
            )
        );

        $component = $diFinder->build('SomeInvalidComponent');
    }

    /**
     * Will create a stub with several methods and defined return values.
     * definition:
     * [
     *   'myMethod' => 'somevalue',
     *   'myOtherMethod' => $callback,
     *   'anotherMethod' => function ($x) use ($y) {},
     * ]
     *
     * @param string $class
     * @param array $methods
     * @param string $className classname for mock object
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function stub($class, array $methods = [], $className = '')
    {
        $builder = $this->getMockBuilder($class)
            ->disableOriginalConstructor();

        if (!empty($methods)) {
            $builder->setMethods(array_keys($methods));
        }

        if ('' !== $className) {
            $builder->setMockClassName($className);
        }

        $stub = $builder->getMock();
        foreach ($methods as $method => $value) {
            if (is_callable($value)) {
                $stub->expects($this->any())->method($method)->willReturnCallback($value);
            } else {
                $stub->expects($this->any())->method($method)->willReturn($value);
            }
        }

        return $stub;
    }
}
