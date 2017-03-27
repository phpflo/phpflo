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

$definition = new PhpFlo\Fbp\FbpDefinition(['properties' => ['name' => 'linecount']]);

// Add nodes to the graph
$graph = new PhpFlo\Graph($definition);
$graph
    ->addNode("Read File", "ReadFile")
    ->addNode("Split by Lines", "SplitStr")
    ->addNode("Count Lines", "Counter")
    ->addNode("Display", "Output");

// Add connections between nodes
$graph
    ->addEdge("Read File", "out", "Split by Lines", "in")
    ->addEdge("Read File", "error", "Display", "in")
    ->addEdge("Split by Lines", "out", "Count Lines", "in")
    ->addEdge("Count Lines", "count", "Display", "in");

// Kick-start the process by sending filename to Read File
$graph->addInitial($fileName, "Read File", "source");

$builder = new PhpFlo\Builder\ComponentFactory();

//echo $graph->toJson();

// Make the graph "live"
$network = new PhpFlo\Network($builder);
$network->boot($graph);
