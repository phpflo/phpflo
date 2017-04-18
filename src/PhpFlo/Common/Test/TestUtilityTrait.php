<?php
/*
 * This file is part of the phpflo/phpflo-flowtrace package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Common\Test;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Core\Interaction\InternalSocket;

/**
 * Mock class helper.
 *
 * @package PhpFlo\Common\Test
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait TestUtilityTrait
{
    /**
     * Will create a stub with several methods and defined return values.
     * definition:
     * [
     *   'myMethod' => 'somevalue',
     *   'myOtherMethod' => $callback,
     *   'anotherMethod' => function ($x) use ($y) {},
     * ]
     *
     * @param string $class
     * @param array $methods
     * @param string $className classname for mock object
     * @param bool $forceMethods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function stub(
        string $class,
        array $methods = [],
        string $className = '',
        bool $forceMethods = false
    ): \PHPUnit_Framework_MockObject_MockObject {
        $builder = $this->getMockBuilder($class)
            ->disableOriginalConstructor();

        if (!empty($methods) && $forceMethods) {
            $builder->setMethods(array_keys($methods));
        }

        if ('' !== $className) {
            $builder->setMockClassName($className);
        }

        $stub = $builder->getMock();
        foreach ($methods as $method => $value) {
            if (is_callable($value)) {
                $stub->expects($this->any())->method($method)->willReturnCallback($value);
            } else {
                $stub->expects($this->any())->method($method)->willReturn($value);
            }
        }

        return $stub;
    }

    /**
     * @var array
     */
    private $outPortSockets;

    /**
     * Fake-connect socket to port.
     *
     * @param ComponentInterface $component
     * @return ComponentInterface
     */
    protected function connectInPorts(ComponentInterface $component): ComponentInterface
    {
        foreach ($component->inPorts() as $alias => $inPort) {
            $inPort->attach($this->stub(InternalSocket::class));
        }

        return $component;
    }

    /**
     * Fake-connect socket to port and add a storage for later value checks.
     *
     * @param ComponentInterface $component
     * @return ComponentInterface
     */
    protected function connectOutPorts(ComponentInterface $component): ComponentInterface
    {
        $this->outPortSockets = [];
        foreach ($component->outPorts() as $port) {
            $socket = $this->stub(
                InternalSocket::class,
                [
                    'isConnected' => true,
                ]
            );
            $socket->expects($this->any())
                ->method('send')
                ->willReturnCallback(
                    \Closure::bind(
                        function ($data) {
                            $this->data[] = $data;
                        },
                        $socket
                    )
                );
            $this->outPortSockets[$port->getName()] = $socket;
            $socket->from = [];
            $socket->to = [];
            $port->attach($socket);
        }

        return $component;
    }

    /**
     * @param ComponentInterface $component
     * @return ComponentInterface
     */
    public function connectPorts(ComponentInterface $component): ComponentInterface
    {
        $this->connectInPorts($component);
        $this->connectOutPorts($component);

        return $component;
    }

    /**
     * @param string $port
     * @return array|mixed
     */
    protected function getOutPortData(string $port = '')
    {
        if ('' !== $port) {
            if (array_key_exists($port, $this->outPortSockets)) {
                return $this->outPortSockets[$port]->data;
            }
        }

        return $this->outPortSockets;
    }

    /**
     * @param string $port
     * @return bool
     */
    protected function wasCalled(string $port): bool
    {
        return !empty($this->outPortSockets[$port]);
    }
}
