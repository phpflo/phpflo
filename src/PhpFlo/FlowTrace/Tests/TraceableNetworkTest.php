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
namespace PhpFlo\Tests;

use PhpFlo\Common\HookableNetworkInterface;
use PhpFlo\Common\NetworkInterface;
use PhpFlo\Core\Graph;
use PhpFlo\FlowTrace\Test\TestCase;
use PhpFlo\FlowTrace\TraceableNetwork;
use Psr\Log\LoggerInterface;

class TraceableNetworkTest extends TestCase
{
    use TestUtilityTrait;

    public function testInstance()
    {
        //$this->markTestSkipped('Needs change in phpflo');
        $traceableNetwork = new TraceableNetwork(
            $this->stub(NetworkInterface::class),
            $this->stub(LoggerInterface::class)
        );
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->getNetwork());
    }

    public function testInterface()
    {
        $network = $this->stub(NetworkInterface::class);
        $hookableNetwork = $this->stub(
            NetworkInterface::class,
            [
                'getGraph' => $this->stub(Graph::class),
                'shutdown' => $network,
                'hook' => $network,
                'hooks' => [],
                'uptime' => new \DateInterval('P2Y4DT6H8M'),
                'addNode' => $network,
                'removeNode' => $network,
                'getNode' => [],
                'addEdge' => $network,
                'removeEdge' => $network,
                'boot' => $network,
                'run' => $network,
            ]
        );

        //$this->markTestSkipped('Needs change in phpflo');
        $traceableNetwork = new TraceableNetwork(
            $hookableNetwork,
            $this->stub(LoggerInterface::class)
        );

        // adapter interface
        $this->assertInstanceOf(HookableNetworkInterface::class, $traceableNetwork->getNetwork());

        // network interface
        $this->assertInstanceOf(Graph::class, $traceableNetwork->getGraph(), 'getGraph failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->shutdown(), 'shutdown failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->hook('data', 'test', function() {return true;}), 'hook failed');
        $this->assertTrue(is_array($traceableNetwork->hooks()), 'hooks failed');
        $this->assertInstanceOf(\Dateinterval::class, $traceableNetwork->uptime(), 'uptime failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->addNode([]), 'addNode failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->removeNode([]), 'removeNode failed');
        $this->assertTrue(is_array($traceableNetwork->getNode('id')), 'getNode failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->addEdge([]), 'addEdge failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->removeEdge([]), 'removeEdge failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->boot($this->stub('PhpFlo\Graph')), 'boot failed');
        $this->assertInstanceOf(NetworkInterface::class, $traceableNetwork->run([], 'node', 'port'), 'run failed');
    }
}
