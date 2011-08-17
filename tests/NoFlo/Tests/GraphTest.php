<?php
namespace NoFlo\Tests;

use NoFlo\Graph;

class GraphTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadFile()
    {
        $graph = Graph::loadFile(__DIR__.'/count.json');
        $readFile = $graph->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);
    }
}
