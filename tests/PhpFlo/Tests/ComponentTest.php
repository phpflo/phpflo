<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Tests;

use PhpFlo\Component;

class ComponentTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $component = new Component();

        $this->assertInstanceOf('\PhpFlo\ComponentInterface', $component);
    }

    public function testRegistryInstantiation()
    {
        $component = new Component();

        $this->assertInstanceOf('\PhpFlo\PortRegistry', $component->inPorts());
        $this->assertInstanceOf('\PhpFlo\PortRegistry', $component->outPorts());
    }
}
