<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Common;

use PhpFlo\Graph;

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
    const CONNECTION_SOURCE = 'src';
    const CONNECTION_TARGET = 'tgt';
    const EVENT_ADD = 'add.node';
    const EVENT_REMOVE = 'remove.node';
    const EVENT_ADD_EDGE = 'add.edge';
    const EVENT_REMOVE_EDGE = 'remove.edge';

    /**
     * @param array $edge
     * @return Network
     * @throws InvalidDefinitionException
     */
    public function addEdge(array $edge);

    /**
     * @param array $node
     * @return $this
     * @throws InvalidDefinitionException
     */
    public function addNode(array $node);

    /**
     * Add a flow definition as Graph object or definition file/string
     * and initialize the network processes/connections
     *
     * @param mixed $graph
     * @return Network
     * @throws InvalidDefinitionException
     */
    public function boot($graph);

    /**
     * @return null|Graph
     */
    public function getGraph();

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getNode($id);

    /**
     * @param array $edge
     * @return $this
     */
    public function removeEdge(array $edge);

    /**
     * @param array $node
     * @return $this
     */
    public function removeNode(array $node);

    /**
     * Add initialization data
     *
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return $this
     * @throws FlowException
     */
    public function run($data, $node, $port);

    /**
     * Cleanup network state after runs.
     *
     * @return $this
     */
    public function shutdown();

    /**
     * @return bool|\DateInterval
     */
    public function uptime();
}
