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

use PhpFlo\Component;
use PhpFlo\ComponentRegistry;
use PhpFlo\Exception\ComponentException;
use PhpFlo\Exception\ComponentNotFoundException;

class ComponentRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $registry = new ComponentRegistry();

        $this->assertInstanceOf(ComponentRegistry::class, $registry);
    }

    public function testAddGet()
    {
        $registry = new ComponentRegistry();

        $component = $this->getMockBuilder(Component::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry->add($component, 'mytest');

        $this->assertInstanceOf(Component::class, $registry->get('mytest'));
    }

    /**
     * @expectedException \PhpFlo\Exception\ComponentException
     */
    public function testAddException()
    {
        $registry = new ComponentRegistry();

        $component = $this->getMockBuilder(Component::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registry->add($component, 'mytest');
        $registry->add($component, 'mytest');
    }

    /**
     * @expectedException \PhpFlo\Exception\ComponentNotFoundException
     */
    public function testGetException()
    {
        $registry = new ComponentRegistry();
        $registry->get('catch_me_if_you_can');
    }
}
