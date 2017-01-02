<?php
namespace Tests\PhpFlo;

use PhpFlo\Builder\ComponentFactory;
use PhpFlo\Network;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadFile()
    {
        $builder = new ComponentFactory();

        $network = new Network($builder);
        $network
            ->boot(__DIR__.'/../../examples/linecount/count.json');

        $readFile = $network->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);
    }
}
