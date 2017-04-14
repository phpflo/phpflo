<?php
namespace Test\PhpFlo\Core\Builder;

use PhpFlo\Core\Builder\ComponentDiFinder;
use PhpFlo\Common\ComponentInterface;
use PhpFlo\Core\Test\TestCase;
use Psr\Container\ContainerInterface;

class ComponentDiFinderTest extends TestCase
{

    public function testInstance()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(ContainerInterface::class)
        );

        $this->assertInstanceOf(ComponentDiFinder::class, $diFinder);
    }

    public function testFindComponentInDi()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(
                ContainerInterface::class,
                [
                    'get' => $this->stub(ComponentInterface::class, [], 'SomeComponent'),
                    'has' => false,
                ]
            )
        );

        $component = $diFinder->build('SomeComponent');
        $this->assertInstanceOf('SomeComponent', $component);
    }

    /**
     * @expectedException \PhpFlo\Common\Exception\InvalidDefinitionException
     * @expectedExceptionMessage Component SomeInvalidComponent doesn't appear to be a valid PhpFlo component
     */
    public function testInvalidComponent()
    {
        $diFinder = new ComponentDiFinder(
            $this->stub(
                ContainerInterface::class,
                [
                    'get' => $this->stub('SomeInvalidComponent'),
                    'has' => false,
                ]
            )
        );

        $diFinder->build('SomeInvalidComponent');
    }
}
