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
 * @author Marc Aschmann <maschmann@gmail.com>
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
        if (isset($this->processes[$node[self::NODE_ID]])) {
            return $this;
        }

        $process = [];
        $process[self::NODE_ID] = $node[self::NODE_ID];

        if (isset($node[self::COMPONENT])) {
            $process[self::COMPONENT] = $this->builder->build($node[self::COMPONENT]);
        }

        $this->processes[$node[self::NODE_ID]] = $process;

        return $this;
    }

    /**
     * @param array $node
     * @return $this
     */
    public function removeNode(array $node)
    {
        if (isset($this->processes[$node[self::NODE_ID]])) {
            unset($this->processes[$node[self::NODE_ID]]);
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
        if (!isset($edge[self::SOURCE][self::NODE])) {
            return $this->addInitial(
                $edge[self::SOURCE][self::DATA],
                $edge[self::TARGET][self::NODE],
                $edge[self::TARGET][self::PORT]
            );
        }

        $from = $this->getNode($edge[self::SOURCE][self::NODE]);
        if (!$from) {
            throw new InvalidDefinitionException(
                "No process defined for outbound node {$edge[self::SOURCE][self::NODE]}"
            );
        }

        $to = $this->getNode($edge[self::TARGET][self::NODE]);
        if (!$to) {
            throw new InvalidDefinitionException(
                "No process defined for inbound node {$edge[self::TARGET][self::NODE]}"
            );
        }

        $socket = $this->connectPorts($from, $to, $edge[self::SOURCE][self::PORT], $edge[self::TARGET][self::PORT]);
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
            if ($edge[self::TARGET][self::NODE] == $connection->to[self::PROCESS][self::NODE_ID]
                && $edge[self::TARGET][self::PORT] == $connection->to[self::PROCESS][self::PORT]
            ) {
                $connection->to[self::PROCESS][self::COMPONENT]
                    ->inPorts()
                    ->get($edge[self::TARGET][self::PORT])
                    ->detach($connection);
                $this->connections = array_splice($this->connections, $index, 1);
            }

            if (isset($edge[self::SOURCE][self::NODE])) {
                if ($edge[self::SOURCE][self::NODE] == $connection->from[self::PROCESS][self::NODE_ID]
                    && $edge[self::SOURCE][self::PORT] == $connection->from[self::PROCESS][self::PORT]
                ) {
                    $connection->from[self::PROCESS][self::COMPONENT]
                        ->inPorts()
                        ->get($edge[self::SOURCE][self::PORT])
                        ->detach($connection);
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
            self::SOURCE => [
                self::DATA => $data,
            ],
            self::TARGET => [
                self::NODE => $node,
                self::PORT => $port,
            ],
        ];

        $to = $this->getNode($initializer[self::TARGET][self::NODE]);
        if (!$to) {
            throw new InvalidDefinitionException(
                "No process defined for inbound node {$initializer[self::TARGET][self::NODE]}"
            );
        }

        $socket = $this->addHooks(new InternalSocket());
        $port = $this->connectInboundPort($socket, $to, $initializer[self::TARGET][self::PORT]);
        $socket->connect();
        $socket->send($initializer[self::SOURCE][self::DATA]);

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
            $process[self::COMPONENT]->shutdown();
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

        $graph->on(self::EVENT_ADD, [$this, 'addNode']);
        $graph->on(self::EVENT_REMOVE, [$this, 'removeNode']);
        $graph->on(self::EVENT_ADD_EDGE, [$this, 'addEdge']);
        $graph->on(self::EVENT_REMOVE_EDGE, [$this, 'removeEdge']);

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
                $initializer[self::SOURCE][self::DATA],
                $initializer[self::TARGET][self::NODE],
                $initializer[self::TARGET][self::PORT]
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
        if (!$process[self::COMPONENT]->inPorts()->has($port)) {
            throw new InvalidDefinitionException("No inport {$port} defined for process {$process[self::NODE_ID]}");
        }

        $socket->to(
            [
                self::PROCESS => $process,
                self::PORT => $port,
            ]
        );

        return $process[self::COMPONENT]
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
     * @return NetworkInterface
     * @throws IncompatibleDatatypeException
     * @throws InvalidDefinitionException
     */
    private function connectPorts(array $from, array $to, $edgeFrom, $edgeTo)
    {
        if (!$from[self::COMPONENT]->outPorts()->has($edgeFrom)) {
            throw new InvalidDefinitionException("No outport {$edgeFrom} defined for process {$from[self::NODE_ID]}");
        }

        if (!$to[self::COMPONENT]->inPorts()->has($edgeTo)) {
            throw new InvalidDefinitionException("No inport {$edgeTo} defined for process {$to[self::NODE_ID]}");
        }

        $socket = $this->addHooks(
            new InternalSocket(
                [
                    self::PROCESS => $from,
                    self::PORT => $edgeFrom,
                ],
                [
                    self::PROCESS => $to,
                    self::PORT => $edgeTo,
                ]
            )
        );

        $fromType = $from[self::COMPONENT]->outPorts()->get($edgeFrom)->getAttribute('datatype');
        $toType = $to[self::COMPONENT]->inPorts()->get($edgeTo)->getAttribute('datatype');

        if (!$this->hasValidPortType($fromType)) {
            throw new InvalidDefinitionException(
                "Process {$from[self::NODE_ID]} has invalid outport type {$fromType}. Valid types: " .
                implode(', ', Port::$datatypes)
            );
        }

        if (!$this->hasValidPortType($toType)) {
            throw new InvalidDefinitionException(
                "Process {$to[self::NODE_ID]} has invalid outport type {$toType}. Valid types: " .
                implode(', ', Port::$datatypes)
            );
        }

        // compare out and in ports for datatype definitions
        if (!Port::isCompatible($fromType, $toType)) {
            throw new IncompatibleDatatypeException(
                "Process {$from[self::NODE_ID]}: outport type \"{$fromType}\" of port \"{$edgeFrom}\" ".
                "does not match {$to[self::NODE_ID]} inport type \"{$toType}\" of port \"{$edgeTo}\""
            );
        }

        $from[self::COMPONENT]->outPorts()->get($edgeFrom)->attach($socket);
        $to[self::COMPONENT]->inPorts()->get($edgeTo)->attach($socket);

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
