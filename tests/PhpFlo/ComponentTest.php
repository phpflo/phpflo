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
use PhpFlo\Interaction\PortRegistry;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $component = new Component();

        $this->assertInstanceOf(Component::class, $component);
    }

    public function testRegistryInstantiation()
    {
        $component = new Component();

        $this->assertInstanceOf(PortRegistry::class, $component->inPorts());
        $this->assertInstanceOf(PortRegistry::class, $component->outPorts());
    }

    public function testShutdown()
    {
        $component = new Component();

        $component->inPorts()->add('source', []);
        $component->outPorts()->add('out', []);
        $this->assertSame($component, $component->shutdown());
    }
}
