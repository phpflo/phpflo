<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo;

use PhpFlo\Common\ComponentBuilderInterface;
use PhpFlo\Common\HookableNetworkTrait;
use PhpFlo\Common\NetworkInterface;
use PhpFlo\Common\SocketInterface;
use PhpFlo\Exception\FlowException;
use PhpFlo\Exception\IncompatibleDatatypeException;
use PhpFlo\Exception\InvalidDefinitionException;
use PhpFlo\Exception\InvalidTypeException;
use PhpFlo\Interaction\InternalSocket;
use PhpFlo\Interaction\Port;

/**
 * Builds the concrete network based on graph.
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Network implements NetworkInterface
{

    use HookableNetworkTrait;

    /**
     * @var array
     */
    private $processes;

    /**
     * @var array
     */
    private $connections;

    /**
     * @var Graph
     */
    private $graph;

    /**
     * @var \DateTime
     */
    private $startupDate;

    /**
     * @var ComponentBuilderInterface
     */
    private $builder;

    /**
     * @param ComponentBuilderInterface $builder
     */
    public function __construct(ComponentBuilderInterface $builder)
    {
        //$this->graph = $graph;
        $this->builder = $builder;
        $this->startup();

        $this->processes = [];
        $this->connections = [];
    }

    /**
     * @return bool|\DateInterval
     */
    public function uptime()
    {
        return $this->startupDate->diff($this->createDateTimeWithMilliseconds());
    }

    /**
     * @param array $node
     * @return $this
     * @throws InvalidDefinitionException
     */
    public function addNode(array $node)
    {
        if (isset($this->processes[$node['id']])) {
            return $this;
        }

        $process = [];
        $process['id'] = $node['id'];

        if (isset($node['component'])) {
            $process['component'] = $this->builder->build($node['component']);
        }

        $this->processes[$node['id']] = $process;

        return $this;
    }

    /**
     * @param array $node
     * @return $this
     */
    public function removeNode(array $node)
    {
        if (isset($this->processes[$node['id']])) {
            unset($this->processes[$node['id']]);
        }

        return $this;
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getNode($id)
    {
        if (!isset($this->processes[$id])) {
            return null;
        }

        return $this->processes[$id];
    }

    /**
     * @return null|Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * @param array $edge
     * @return $this
     * @throws InvalidDefinitionException
     */
    public function addEdge(array $edge)
    {
        if (!isset($edge['from']['node'])) {
            return $this->addInitial(
                $edge['from']['data'],
                $edge['to']['node'],
                $edge['to']['port']
            );
        }

        $from = $this->getNode($edge['from']['node']);
        if (!$from) {
            throw new InvalidDefinitionException("No process defined for outbound node {$edge['from']['node']}");
        }

        $to = $this->getNode($edge['to']['node']);
        if (!$to) {
            throw new InvalidDefinitionException("No process defined for inbound node {$edge['to']['node']}");
        }

        $socket = $this->connectPorts($from, $to, $edge['from']['port'], $edge['to']['port']);
        $this->connections[] = $socket;

        return $this;
    }

    /**
     * @param array $edge
     * @return $this
     */
    public function removeEdge(array $edge)
    {
        foreach ($this->connections as $index => $connection) {
            if ($edge['to']['node'] == $connection->to['process']['id'] && $edge['to']['port'] == $connection->to['process']['port']) {
                $connection->to['process']['component']->inPorts()->get($edge['to']['port'])->detach($connection);
                $this->connections = array_splice($this->connections, $index, 1);
            }

            if (isset($edge['from']['node'])) {
                if ($edge['from']['node'] == $connection->from['process']['id'] && $edge['from']['port'] == $connection->from['process']['port']) {
                    $connection->from['process']['component']->inPorts()->get($edge['from']['port'])->detach($connection);
                    $this->connections = array_splice($this->connections, $index, 1);
                }
            }
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return $this
     * @throws InvalidDefinitionException
     */
    protected function addInitial($data, $node, $port)
    {
        $initializer = [
            'from' => [
                'data' => $data,
            ],
            'to' => [
                'node' => $node,
                'port' => $port,
            ],
        ];

        $to = $this->getNode($initializer['to']['node']);
        if (!$to) {
            throw new InvalidDefinitionException("No process defined for inbound node {$initializer['to']['node']}");
        }

        $socket = $this->addHooks(new InternalSocket());
        $port = $this->connectInboundPort($socket, $to, $initializer['to']['port']);
        $socket->connect();
        $socket->send($initializer['from']['data']);

        // cleanup initialization
        $socket->disconnect();
        $port->detach($socket);

        $this->connections[] = $socket;

        return $this;
    }

    /**
     * Cleanup network state after runs.
     *
     * @return $this
     */
    public function shutdown()
    {
        foreach ($this->processes as $process) {
            $process['component']->shutdown();
        }

        // explicitly destroy the $connections
        foreach ($this->connections as $connection) {
            $connection = null;
        }

        $this->graph = null;
        $this->processes = [];
        $this->startupDate = null;
        $this->connections = [];

        return $this;
    }

    /**
     * Add initialization data
     *
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return $this
     * @throws FlowException
     */
    public function run($data, $node, $port)
    {
        if (empty($this->graph)) {
            throw new FlowException(
                "Graph is not yet initialized!"
            );
        }

        $this->graph->addInitial($data, $node, $port);

        return $this;
    }

    /**
     * Add a flow definition as Graph object or definition file/string
     * and initialize the network processes/connections
     *
     * @param mixed $graph
     * @return $this
     * @throws InvalidTypeException
     */
    public function boot($graph)
    {
        switch (true) {
            case (is_a(Graph::class, $graph)):
                break;
            case (is_file($graph)):
                $graph = Graph::loadFile($graph);
                break;
            case (is_string($graph)):
                $graph = Graph::loadString($graph);
                break;
            default:
                throw new InvalidTypeException(
                    "Graph has to be either a Graph object or a compatible definition file/string"
                );
        }

        $graph->on('add.node', [$this, 'addNode']);
        $graph->on('remove.node', [$this, 'removeNode']);
        $graph->on('add.edge', [$this, 'addEdge']);
        $graph->on('remove.edge', [$this, 'removeEdge']);

        /** @todo think of caching graphs here, maybe */
        $this->graph = $graph;
        $this->loadGraph($graph);

        return $this;
    }

    /**
     * Load Graph into Network
     *
     * @param Graph $graph
     */
    private function loadGraph(Graph $graph)
    {
        foreach ($graph->nodes as $node) {
            $this->addNode($node);
        }

        foreach ($graph->edges as $edge) {
            $this->addEdge($edge);
        }

        foreach ($graph->initializers as $initializer) {
            $this->addInitial(
                $initializer['from']['data'],
                $initializer['to']['node'],
                $initializer['to']['port']
            );
        }
    }

    /**
     * @param SocketInterface $socket
     * @param array $process
     * @param Port $port
     * @throws InvalidDefinitionException
     * @return mixed
     */
    private function connectInboundPort(SocketInterface $socket, array $process, $port)
    {
        if (!$process['component']->inPorts()->has($port)) {
            throw new InvalidDefinitionException("No inport {$port} defined for process {$process['id']}");
        }

        $socket->to(
            [
                'process' => $process,
                'port' => $port,
            ]
        );

        return $process['component']
            ->inPorts()
            ->get($port)
            ->attach($socket);
    }

    /**
     * Connect out to inport and compare data types.
     *
     * @param array $from
     * @param array $to
     * @param string $edgeFrom
     * @param string $edgeTo
     * @return SocketInterface
     * @throws IncompatibleDatatypeException
     * @throws InvalidDefinitionException
     */
    private function connectPorts(array $from, array $to, $edgeFrom, $edgeTo)
    {
        if (!$from['component']->outPorts()->has($edgeFrom)) {
            throw new InvalidDefinitionException("No outport {$edgeFrom} defined for process {$from['id']}");
        }

        if (!$to['component']->inPorts()->has($edgeTo)) {
            throw new InvalidDefinitionException("No inport {$edgeTo} defined for process {$to['id']}");
        }

        $socket = $this->addHooks(
            new InternalSocket(
                [
                    'process' => $from,
                    'port' => $edgeFrom,
                ],
                [
                    'process' => $to,
                    'port' => $edgeTo,
                ]
            )
        );

        $fromType = $from['component']->outPorts()->get($edgeFrom)->getAttribute('datatype');
        $toType = $to['component']->inPorts()->get($edgeTo)->getAttribute('datatype');

        if (!$this->hasValidPortType($fromType)) {
            throw new InvalidDefinitionException(
                "Process {$from['id']} has invalid outport type {$fromType}. Valid types: " .
                implode(', ', Port::$datatypes)
            );
        }

        if (!$this->hasValidPortType($toType)) {
            throw new InvalidDefinitionException(
                "Process {$to['id']} has invalid outport type {$toType}. Valid types: " .
                implode(', ', Port::$datatypes)
            );
        }

        // compare out and in ports for datatype definitions
        if (!Port::isCompatible($fromType, $toType)) {
            throw new IncompatibleDatatypeException(
                "Process {$from['id']}: outport type \"{$fromType}\" of port \"{$edgeFrom}\" ".
                "does not match {$to['id']} inport type \"{$toType}\" of port \"{$edgeTo}\""
            );
        }

        $from['component']->outPorts()->get($edgeFrom)->attach($socket);
        $to['component']->inPorts()->get($edgeTo)->attach($socket);

        return $this;
    }

    /**
     * @return \DateTime
     */
    private function createDateTimeWithMilliseconds()
    {
        return \DateTime::createFromFormat('U.u', sprintf('%.6f', microtime(true)));
    }

    /**
     * Set startup timer.
     */
    private function startup()
    {
        $this->startupDate = $this->createDateTimeWithMilliseconds();
    }

    /**
     * Check datatype vs. defined types.
     *
     * @param string $type
     * @return bool
     */
    private function hasValidPortType($type)
    {
        return in_array($type, Port::$datatypes);
    }
}
