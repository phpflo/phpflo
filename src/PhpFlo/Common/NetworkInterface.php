<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Common;

use PhpFlo\Core\Graph;

/**
 * Interface NetworkInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface NetworkInterface extends HookableNetworkInterface
{
    const SOURCE = 'from';
    const TARGET = 'to';
    const NODE_ID = 'id';
    const COMPONENT = 'component';
    const PROCESS = 'process';
    const DATA = 'data';
    const NODE = 'node';
    const PORT = 'port';
    const CONNECT = 'connect';
    const DISCONNECT = 'disconnect';
    const SHUTDOWN = 'shutdown';
    const DETACH = 'detach';
    const CONNECTION_SOURCE = 'src';
    const CONNECTION_TARGET = 'tgt';
    const EVENT_ADD = 'add.node';
    const EVENT_REMOVE = 'remove.node';
    const EVENT_ADD_EDGE = 'add.edge';
    const EVENT_REMOVE_EDGE = 'remove.edge';
    const BEGIN_GROUP = 'begin.group';
    const END_GROUP = 'end.group';

    /**
     * @param array $edge
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function addEdge(array $edge) : NetworkInterface;

    /**
     * @param array $node
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function addNode(array $node) : NetworkInterface;

    /**
     * Add a flow definition as Graph object or definition file/string
     * and initialize the network processes/connections
     *
     * @param mixed $graph
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function boot($graph) : NetworkInterface;

    /**
     * @return null|Graph
     */
    public function getGraph();

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getNode(string $id);

    /**
     * @param array $edge
     * @return NetworkInterface
     */
    public function removeEdge(array $edge) : NetworkInterface;

    /**
     * @param array $node
     * @return NetworkInterface
     */
    public function removeNode(array $node) : NetworkInterface;

    /**
     * Add initialization data
     *
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return NetworkInterface
     * @throws FlowException
     */
    public function run($data, string $node, string $port) : NetworkInterface;

    /**
     * Cleanup network state after runs.
     *
     * @return NetworkInterface
     */
    public function shutdown() : NetworkInterface;

    /**
     * @return bool|\DateInterval
     */
    public function uptime();
}
