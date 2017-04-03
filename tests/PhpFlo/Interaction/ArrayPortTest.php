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


use PhpFlo\Interaction\ArrayPort;

class ArrayPortTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $port = new ArrayPort('testport', []);

        $this->assertInstanceOf(ArrayPort::class, $port);
    }
}
