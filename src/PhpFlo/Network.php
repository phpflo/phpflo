<?php
namespace PhpFlo;

class Network
{
    private $processes = array();
    private $connections = array();
    private $graph = null;
    private $startupDate = null;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
        $this->startupDate = new \DateTime();

        $this->graph->on('addNode', array($this, 'addNode'));
        $this->graph->on('removeNode', array($this, 'removeNode'));
        $this->graph->on('addEdge', array($this, 'addEdge'));
        $this->graph->on('removeEdge', array($this, 'removeEdge'));
    }

    public function uptime()
    {
        return $this->startupDate->diff(new \DateTime());
    }

    public function addNode(array $node)
    {
        if (isset($this->processes[$node['id']])) {
            return;
        }

        $process = array();
        $process['id'] = $node['id'];

        if (isset($node['component'])) {
            $componentClass = $node['component'];
            if (!class_exists($componentClass) && strpos($componentClass, '\\') === false) {
                $componentClass = "PhpFlo\\Component\\{$componentClass}";
                if (!class_exists($componentClass)) {
                    throw new \InvalidArgumentException("Component class {$componentClass} not found");
                }
            }
            $component = new $componentClass();
            if (!$component instanceof ComponentInterface) {
                throw new \InvalidArgumentException("Component {$node['component']} doesn't appear to be a valid PhpFlo component");
            }
            $process['component'] = new $componentClass();
        }

        $this->processes[$node['id']] = $process;
    }

    public function removeNode(array $node)
    {
        if (!isset($this->processes[$node['id']])) {
            return;
        }

        unset($this->processes[$node['id']]);
    }

    public function getNode($id)
    {
        if (!isset($this->processes[$id])) {
            return null;
        }

        return $this->processes[$id];
    }

    public function getGraph()
    {
        return $this->graph;
    }

    private function connectPort(SocketInterface $socket, array $process, $port, $inbound)
    {
        if ($inbound) {
            $socket->to = array(
                'process' => $process,
                'port' => $port,
            );

            if (!isset($process['component']->inPorts[$port])) {
                throw new \InvalidArgumentException("No inport {$port} defined for process {$process['id']}");
            }

            return $process['component']->inPorts[$port]->attach($socket);
        }

        $socket->from = array(
            'process' => $process,
            'port' => $port,
        );

        if (!isset($process['component']->outPorts[$port])) {
            throw new \InvalidArgumentException("No outport {$port} defined for process {$process['id']}");
        }

        return $process['component']->outPorts[$port]->attach($socket);
    }

    public function addEdge(array $edge)
    {
        if (!isset($edge['from']['node'])) {
            return $this->addInitial($edge);
        }
        $socket = new InternalSocket();

        $from = $this->getNode($edge['from']['node']);
        if (!$from) {
            throw new \InvalidArgumentException("No process defined for outbound node {$edge['from']['node']}");
        }

        $to = $this->getNode($edge['to']['node']);
        if (!$to) {
            throw new \InvalidArgumentException("No process defined for inbound node {$edge['to']['node']}");
        }

        $this->connectPort($socket, $from, $edge['from']['port'], false);
        $this->connectPort($socket, $to, $edge['to']['port'], true);

        $this->connections[] = $socket;
    }

    public function removeEdge(array $edge) {
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

    public function addInitial(array $initializer)
    {
        $socket = new InternalSocket();
        $to = $this->getNode($initializer['to']['node']);
        if (!$to) {
            throw new \InvalidArgumentException("No process defined for inbound node {$initializer['to']['node']}");
        }

        $this->connectPort($socket, $to, $initializer['to']['port'], true);
        $socket->connect();
        $socket->send($initializer['from']['data']);
        $socket->disconnect();

        $this->connections[] = $socket;
    }

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

    public static function loadFile($file)
    {
        $graph = Graph::loadFile($file);
        return Network::create($graph);
    }
}
