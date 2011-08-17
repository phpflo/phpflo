<?php
namespace PhpFlo\Tests;

use PhpFlo\Network;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadFile()
    {
        $network = Network::loadFile(__DIR__.'/count.json');
        $readFile = $network->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);
    }
}
