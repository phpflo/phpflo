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

use PhpFlo\Core\Test\TestCase;
use PhpFlo\Core\Interaction\ArrayPort;

class ArrayPortTest extends TestCase
{
    public function testInstance()
    {
        $port = new ArrayPort('testport', []);

        $this->assertInstanceOf(ArrayPort::class, $port);
    }
}
