#!/usr/bin/env php
<?php
/**
 * Flow-based example of counting lines of a file, roughly equivalent
 * to "wc -l <filename>"
 */

if (!isset($_SERVER['argv'][1])) {
    die("You must provide a filename\n");
}
$fileName = $_SERVER['argv'][1];

// Include standard autoloader
require __DIR__ . '/../../vendor/autoload.php';

$builder = new PhpFlo\Builder\ComponentFactory();

// Load network from graph file
//$network = PhpFlo\Network::loadFile(__DIR__.'/count.fbp', $builder);
$network = new \PhpFlo\Network($builder);
$network
    ->hook(
        'data',
        'trace',
        function ($data, $socket) {
            echo $socket->getId() . print_r($data, true) . "\n";
        }
    )
    ->create(__DIR__.'/count.fbp')
    ->run($fileName, "ReadFile", "source")
    ->shutdown();

// Kick-start the process by sending filename
//$network->getGraph()->addInitial($fileName, "ReadFile", "source");
//$network->shutdown();
