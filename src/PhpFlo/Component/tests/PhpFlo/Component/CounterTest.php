<?php
declare(strict_types=1);
/*
 * This file is part of the phpflo/phpflo-core package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\PhpFlo\Component;

use PhpFlo\Component\Counter;
use PhpFlo\Test\ComponentTestHelperTrait;
use PhpFlo\Test\StubTrait;

class CounterTest extends \PHPUnit_Framework_TestCase
{
    use StubTrait;
    use ComponentTestHelperTrait;

    public function testBehavior()
    {
        $counter = new Counter();
        $this->connectPorts($counter);

        $this->assertTrue($counter->inPorts()->has('in'));
        $this->assertTrue($counter->outPorts()->has('count'));

        $counter->appendCount(1);
        $counter->appendCount(2);
        $counter->appendCount(null);

        $counter->sendCount();

        $countData = $this->getOutPortData('count');
        $this->assertEquals(3, $countData[0]);
    }
}
