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


class HookableNetworkTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetHook()
    {
        $trait = $this->getMockForTrait('\PhpFlo\Common\HookableNetworkTrait');

        $trait->hook(
            'data',
            'somename',
            function () {
                return 'callback1';
            }
        );

        $hooks = $trait->hooks();

        $this->assertTrue(is_array($hooks));
        $this->assertArrayHasKey('data', $hooks);
        $this->assertArrayHasKey('somename', $hooks['data']);
        $this->assertInstanceOf('\Closure', $hooks['data']['somename']);
        $this->assertEquals('callback1', $hooks['data']['somename']());
    }

    /**
     * @expectedException \PhpFlo\Exception\InvalidTypeException
     */
    public function testInvalidEventNameException()
    {
        $trait = $this->getMockForTrait('\PhpFlo\Common\HookableNetworkTrait');

        $trait->hook(
            'i_am_inavlid',
            'somename',
            function () {
                return 'callback1';
            }
        );
    }

    /**
     * @expectedException \PhpFlo\Exception\FlowException
     */
    public function testEventAlreadyExistsException()
    {
        $trait = $this->getMockForTrait('\PhpFlo\Common\HookableNetworkTrait');

        $trait->hook(
            'data',
            'somename',
            function () {
                return 'callback1';
            }
        )->hook(
            'data',
            'somename',
            function () {
                return 'callback1';
            }
        );
    }

    public function testAddHooksToSocket()
    {
        $trait = $this->getMockForTrait('\PhpFlo\Common\HookableNetworkTrait');

        $trait->hook(
            'data',
            'somename',
            function () {
                return 'callback1';
            }
        );

        /** since we want to test the protected method, this is the only way in php -.- */
        $reflector = new \ReflectionClass(get_class($trait));
        $method = $reflector->getMethod('addHooks');
        $method->setAccessible(true);

        $socket = $this->getMockBuilder('\PhpFlo\Interaction\InternalSocket')
            ->disableOriginalConstructor()
            ->getMock();

        // bind closure to socket, to have object access
        $cb = \Closure::bind(
            function () {
                $this->set_listener['data'] = 'somename';
            },
            $socket
        );

        $socket
            ->expects($this->any())
            ->method('on')
            ->willReturnCallback($cb);

        // we just want to test if the $socket->on() method is called
        $socket = $method->invokeArgs($trait, [$socket]);
        $this->assertEquals('somename', $socket->set_listener['data']);
    }
}
