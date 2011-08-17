<?php
namespace PhpFlo;

use Evenement\EventEmitter;

class Graph extends EventEmitter
{
    private $name = "";
    public $nodes = array();
    public $edges = array();
    public $initializers = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addNode($id, $component)
    {
        $node = array(
            'id' => $id,
            'component' => $component
        );

        $this->nodes[$id] = $node;
        $this->emit('addNode', array($node));
    }

    public function removeNode($id)
    {
        foreach ($this->edges as $edge) {
            if ($edge['from']['node'] == $id) {
                $this->removeEdge($edge);
            }
            if ($edge['to']['node'] == $id) {
                $this->removeEdge($edge);
            }
        }

        foreach ($this->initializers as $initializer) {
            if ($initializer['to']['node'] == $id) {
                $this->removeEdge($initializer);
            }
        }

        $node = $this->nodes[$id];
        $this->emit('removeNode', array($node));
        unset($this->nodes[$id]);
    }

    public function getNode($id)
    {
        if (!isset($this->nodes[$id])) {
            return null;
        }
        return $this->nodes[$id];
    }

    public function addEdge($outNode, $outPort, $inNode, $inPort)
    {
        $edge = array(
            'from' => array(
                'node' => $outNode,
                'port' => $outPort,
            ),
            'to' => array(
                'node' => $inNode,
                'port' => $inPort,
            ),
        );

        $this->edges[] = $edge;
        $this->emit('addEdge', array($edge));
    }

    public function removeEdge($node, $port)
    {
        foreach ($this->edges as $index => $edge) {
            if ($edge['from']['node'] == $node && $edge['from']['port'] == $port) {
                $thia->emit('removeEdge', array($edge));
                $this->edges = array_splice($this->edges, $index, 1); 
            }

            if ($edge['to']['node'] == $node && $edge['to']['port'] == $port) {
                $thia->emit('removeEdge', array($edge));
                $this->edges = array_splice($this->edges, $index, 1);
            }
        }

        foreach ($this->initializers as $index => $initializer) {
            if ($initializer['to']['node'] == $node && $initializer['to']['port'] == $port) {
                $thia->emit('removeEdge', array($initializer));
                $this->initializers = array_splice($this->initializers, $index, 1);
            }
        }
    }

    public function addInitial($data, $node, $port)
    {
        $initializer = array(
            'from' => array(
                'data' => $data,
            ),
            'to' => array(
                'node' => $node,
                'port' => $port,
            ),
        );

        $this->initializers[] = $initializer;
        $this->emit('addEdge', array($initializer));
    }

    public function toJSON()
    {
        $json = array(
            'properties' => array(
                'name' => $this->name,
            ),
            'processes' => array(),
            'connections' => array(),
        );

        foreach ($this->nodes as $node) {
            $json['processes'][$node['id']] = array(
                'component' => $node['component'],
            );
        }

        foreach ($this->edges as $edge) {
            $json['connections'][] = array(
                'src' => array(
                    'process' => $edge['from']['node'],
                    'port' => $edge['from']['port'],
                ),
                'tgt' => array(
                    'process' => $edge['to']['node'],
                    'port' => $edge['to']['port'],
                ),
            );
        }

        foreach ($this->initializers as $initializer) {
            $json['connections'][] = array(
                'data' => $initializer['from']['data'],
                'tgt' => array(
                    'process' => $initializer['to']['node'],
                    'port' => $initializer['to']['port'],
                ),
            );            
        }

        // TODO: JSON_PRETTY_PRINT support in PHP 5.4
        return json_encode($json);
    }

    public static function save($file)
    {
        $stat = file_put_contents($file, $this->toJSON());
        if ($stat === false) {
            return false;
        }
        return true;
    }

    public static function loadFile($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("File {$file} not found");
        }

        $definition = @json_decode(file_get_contents($file));
        if (!$definition) {
            throw new \InvalidArgumentException("Failed to parse PhpFlo graph definition file {$file}");
        }

        $graph = new Graph($definition->properties->name);

        foreach ($definition->processes as $id => $def) {
            $graph->addNode($id, $def->component);
        }

        foreach ($definition->connections as $conn) {
            if (isset($conn->data)) {
                $graph->addInitial($conn->data, $conn->tgt->process, $conn->tgt->port);
                continue;
            }

            $graph->addEdge($conn->src->process, $conn->src->port, $conn->tgt->process, $conn->tgt->port);
        }

        return $graph;
    }
}
