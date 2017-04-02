<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\PhpFlo\Common;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\ComponentTrait;
use PhpFlo\Component;
use PhpFlo\Interaction\PortRegistry;

class ComponentTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testDescriptionAccessor()
    {
        $component = $this->mockComponentTrait();
        $this->assertEquals("", $component->getDescription());
    }

    public function testInPorts()
    {
        $component = $this->mockComponentTrait();
        $this->assertInstanceOf(PortRegistry::class, $component->inPorts());
    }

    public function testOutPorts()
    {
        $component = $this->mockComponentTrait();
        $this->assertInstanceOf(PortRegistry::class, $component->outPorts());
    }

    public function testShutdown()
    {
        $component = $this->mockComponentTrait();
        $component->inPorts()->add('source', []);
        $component->outPorts()->add('out', []);
        $this->assertSame($component, $component->shutdown());
    }

    private function mockComponentTrait()
    {
        return $this->getObjectForTrait(ComponentTrait::class);
    }
}
