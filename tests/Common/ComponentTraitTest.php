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

class ComponentTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testDescriptionAccessor()
    {
        $trait = $this->mockComponentTrait();
        $this->assertEquals("", $trait->getDescription());
    }

    public function testInPorts()
    {
        $trait = $this->mockComponentTrait();
        $this->assertInstanceOf('\PhpFlo\Interaction\PortRegistry', $trait->inPorts());
    }

    public function testOutPorts()
    {
        $trait = $this->mockComponentTrait();
        $this->assertInstanceOf('\PhpFlo\Interaction\PortRegistry', $trait->outPorts());
    }

    private function mockComponentTrait()
    {
        return $this->getMockForTrait('\PhpFlo\Common\ComponentTrait');
    }
}
