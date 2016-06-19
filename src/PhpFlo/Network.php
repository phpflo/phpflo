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

use PhpFlo\Exception\InvalidDefinitionException;

/**
 * Class Network
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Network
{
    /**
     * @var array
     */
    private $processes;

    /**
     * @var array
     */
    private $connections;

    /**
     * @var null
     */
    private $graph;

    /**
     * @var null
     */
    private $startupDate;

    /**
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
        $this->startupDate = $this->createDateTimeWithMilliseconds();

        $this->graph->on('addNode', [$this, 'addNode']);
        $this->graph->on('removeNode', [$this, 'removeNode']);
        $this->graph->on('addEdge', [$this, 'addEdge']);
        $this->graph->on('removeEdge', [$this, 'removeEdge']);

        $this->processes = [];
        $this->connections = [];
    }

    /**
     * @return null|\DateTime
     */
    public function uptime()
    {
        return $this->startupDate->diff($this->createDateTimeWithMilliseconds());
    }

    /**
     * @param array $node
     * @throws InvalidDefinitionException
     */
    public function addNode(array $node)
    {
        if (isset($this->processes[$node['id']])) {
            return;
        }

        $process = [];
        $process['id'] = $node['id'];

        if (isset($node['component'])) {
            $componentClass = $node['component'];
            if (!class_exists($componentClass) && strpos($componentClass, '\\') === false) {
                $componentClass = "PhpFlo\\Component\\{$componentClass}";
                if (!class_exists($componentClass)) {
                    throw new InvalidDefinitionException("Component class {$componentClass} not found");
                }
            }
            $component = new $componentClass();
            if (!$component instanceof ComponentInterface) {
                throw new InvalidDefinitionException("Component {$node['component']} doesn't appear to be a valid PhpFlo component");
            }
            $process['component'] = $component;
        }

        $this->processes[$node['id']] = $process;
    }

    /**
     * @param array $node
     */
    public function removeNode(array $node)
    {
        if (!isset($this->processes[$node['id']])) {
            return;
        }

        unset($this->processes[$node['id']]);
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
     * @return null
     */
    public function getGraph()
    {
        return $this->graph;
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
        $socket->to = [
            'process' => $process,
            'port' => $port,
        ];

        if (!isset($process['component']->inPorts[$port])) {
            throw new InvalidDefinitionException("No inport {$port} defined for process {$process['id']}");
        }

        return $process['component']->inPorts[$port]->attach($socket);
    }

    /**
     * @param SocketInterface $socket
     * @param array $process
     * @param Port $port
     * @throws InvalidDefinitionException
     * @return mixed
     */
    private function connectOutgoingPort(SocketInterface $socket, array $process, $port)
    {
        $socket->from = [
            'process' => $process,
            'port' => $port,
        ];

        if (!isset($process['component']->outPorts[$port])) {
            throw new InvalidDefinitionException("No outport {$port} defined for process {$process['id']}");
        }

        return $process['component']->outPorts[$port]->attach($socket);
    }

    /**
     * @param array $edge
     * @throws InvalidDefinitionException
     */
    public function addEdge(array $edge)
    {
        if (!isset($edge['from']['node'])) {
            return $this->addInitial($edge);
        }
        $socket = new InternalSocket();

        $from = $this->getNode($edge['from']['node']);
        if (!$from) {
            throw new InvalidDefinitionException("No process defined for outbound node {$edge['from']['node']}");
        }

        $to = $this->getNode($edge['to']['node']);
        if (!$to) {
            throw new InvalidDefinitionException("No process defined for inbound node {$edge['to']['node']}");
        }

        $this->connectOutgoingPort($socket, $from, $edge['from']['port']);
        $this->connectInboundPort($socket, $to, $edge['to']['port']);

        $this->connections[] = $socket;
    }

    /**
     * @param array $edge
     */
    public function removeEdge(array $edge)
    {
        foreach ($this->connections as $index => $connection) {
            if ($edge['to']['node'] == $connection->to['process']['id'] && $edge['to']['port'] == $connection->to['process']['port']) {
                $connection->to['process']['component']->inPorts[$edge['to']['port']]->detach($connection);
                $this->connections = array_splice($this->connections, $index, 1);
            }

            if (isset($edge['from']['node'])) {
                if ($edge['from']['node'] == $connection->from['process']['id'] && $edge['from']['port'] == $connection->from['process']['port']) {
                    $connection->from['process']['component']->inPorts[$edge['from']['port']]->detach($connection);
                    $this->connections = array_splice($this->connections, $index, 1);
                }
            }
        }
    }

    /**
     * @param array $initializer
     * @throws InvalidDefinitionException
     */
    public function addInitial(array $initializer)
    {
        $socket = new InternalSocket();
        $to = $this->getNode($initializer['to']['node']);
        if (!$to) {
            throw new InvalidDefinitionException("No process defined for inbound node {$initializer['to']['node']}");
        }

        $this->connectInboundPort($socket, $to, $initializer['to']['port']);
        $socket->connect();
        $socket->send($initializer['from']['data']);
        $socket->disconnect();

        $this->connections[] = $socket;
    }

    /**
     * @param Graph $graph
     * @return Network
     */
    public static function create(Graph $graph)
    {
        $network = new Network($graph);

        foreach ($graph->nodes as $node) {
            $network->addNode($node);
        }

        foreach ($graph->edges as $edge) {
            $network->addEdge($edge);
        }

        foreach ($graph->initializers as $initializer) {
            $network->addInitial($initializer);
        }

        return $network;
    }

    /**
     * Load PhpFlo graph definition from string.
     *
     * @param string $string
     * @return Network
     */
    public static function loadString($string)
    {
        $graph = Graph::loadString($string);

        return Network::create($graph);
    }

    /**
     * Load PhpFlo graph definition from file.
     *
     * @param string $file
     * @return Network
     */
    public static function loadFile($file)
    {
        $graph = Graph::loadFile($file);

        return Network::create($graph);
    }

    /**
     * @return \DateTime
     */
    private function createDateTimeWithMilliseconds()
    {
        return \DateTime::createFromFormat('U.u', sprintf('%.6f', microtime(true)));
    }
}
