<?php
namespace PhpFlo\Tests;

use PhpFlo\Graph;

class GraphTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadFile()
    {
        $graph = Graph::loadFile(__DIR__.'/count.json');
        $readFile = $graph->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);
    }
}
