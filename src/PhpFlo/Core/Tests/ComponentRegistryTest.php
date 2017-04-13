<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\PhpFlo;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Core\ComponentRegistry;
use PhpFlo\Common\Exception\ComponentException;
use PhpFlo\Common\Exception\ComponentNotFoundException;
use PhpFlo\Core\Test\TestCase;

class ComponentRegistryTest extends TestCase
{
    /**
     * @var ComponentRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    public function setUp()
    {
        $this->registry = new ComponentRegistry();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(ComponentRegistry::class, $this->registry);
    }

    public function testAddGet()
    {
        $component = $this->createMock(ComponentInterface::class);
        $this->registry->add($component, 'mytest');

        $this->assertInstanceOf(ComponentInterface::class, $this->registry->get('mytest'));
    }

    public function testAddException()
    {
        $this->expectException(ComponentException::class);

        $component = $this->createMock(ComponentInterface::class);
        $this->registry->add($component, 'mytest');
        $this->registry->add($component, 'mytest');
    }

    public function testGetException()
    {
        $this->expectException(ComponentNotFoundException::class);

        $this->registry = new ComponentRegistry();
        $this->registry->get('catch_me_if_you_can');
    }
}
