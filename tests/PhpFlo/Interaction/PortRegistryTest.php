<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\PhpFlo\Interaction;

use PhpFlo\Common\PortInterface;
use PhpFlo\Interaction\ArrayPort;
use PhpFlo\Interaction\PortRegistry;

class PortRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $registry = new PortRegistry();
        $this->assertInstanceOf(PortRegistry::class, $registry);
    }

    public function testPortInteraction()
    {
        $registry = new PortRegistry();
        $registry->add('test', ['datatype' => 'all']);
        $this->assertTrue($registry->has('test'));

        $port = $registry->get('test');
        $this->assertInstanceOf(PortInterface::class, $port);
        $this->assertEquals('test', $port->getName());


        $registry->add('test2', ['datatype' => 'all', 'addressable' => true]);
        $this->assertInstanceOf(ArrayPort::class, $registry->test2);
        $this->assertEquals('test2', $registry->test2->getName());

        $ports = $registry->get();
        $this->assertTrue(is_array($ports));
        $this->assertCount(2, $ports);

        $registry->remove('test');
        $this->assertFalse($registry->has('test'));
        $this->assertCount(1, $registry->get());
    }

    public function testIterator()
    {
        $registry = new PortRegistry();
        $registry
            ->add('test', ['datatype' => 'all'])
            ->add('test2', ['datatype' => 'all']);

        $this->assertEquals(2, count($registry));

        foreach ($registry as $port) {
            $this->assertInstanceOf(PortInterface::class, $port);
        }
    }
}
