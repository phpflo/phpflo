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

use PhpFlo\Common\ComponentTrait;
use PhpFlo\Interaction\PortRegistry;

class ComponentTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComponentTrait
     */
    private $componentTrait;

    public function setUp()
    {
        $this->componentTrait = $this->getObjectForTrait(ComponentTrait::class);
    }
    
    public function testDescriptionAccessor()
    {
        $this->assertEquals("", $this->componentTrait->getDescription());
    }

    public function testInPorts()
    {
        $this->assertInstanceOf(PortRegistry::class, $this->componentTrait->inPorts());
    }

    public function testOutPorts()
    {
        $this->assertInstanceOf(PortRegistry::class, $this->componentTrait->outPorts());
    }
}
