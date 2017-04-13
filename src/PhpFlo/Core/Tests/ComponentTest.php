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
use PhpFlo\Core\Component;
use PhpFlo\Core\Interaction\PortRegistry;
use PhpFlo\Core\Test\TestCase;

class ComponentTest extends TestCase
{
    public function testInstance()
    {
        $component = new Component();

        $this->assertInstanceOf(ComponentInterface::class, $component);
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
