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
    ) : \PHPUnit_Framework_MockObject_MockObject {
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
}
