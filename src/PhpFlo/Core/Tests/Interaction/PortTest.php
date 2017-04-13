<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\PhpFlo\Core\Interaction;

use PhpFlo\Core\Interaction\Port;
use PhpFlo\Core\Test\TestCase;

class PortTest extends TestCase
{
    public function testInstance()
    {
        $port = new Port('testport', []);

        $this->assertInstanceOf(Port::class, $port);
    }

    public function testBaseFunctionality()
    {
        $port = new Port('source', ['datatype' => 'all']);

        $this->assertEquals('source', $port->getName());
        $this->assertTrue(is_array($port->getAttributes()));
        $this->assertEquals('all', $port->getAttribute('datatype'));
    }
}
