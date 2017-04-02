<?php
namespace Tests\PhpFlo;

use PhpFlo\Graph;

class GraphTest extends \PHPUnit_Framework_TestCase
{
    const JSON_GRAPH_FILEPATH = __DIR__.'/../../examples/linecount/count.json';
    const FBP_GRAPH_FILEPATH = __DIR__.'/../../examples/linecount/count.fbp';

    public function testLoadFile()
    {
        $graph = Graph::loadFile(self::JSON_GRAPH_FILEPATH);
        $readFile = $graph->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);

        $this->assertEquals(4, count($graph->nodes));
    }
}
