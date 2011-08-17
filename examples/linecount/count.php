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

// Autoloading
require_once __DIR__.'/../../vendor/symfony/Component/ClassLoader/UniversalClassLoader.php';
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespace('PhpFlo', __DIR__.'/../../src');
$loader->registerNamespace('Evenement', __DIR__.'/../../vendor/Evenement/src');
$loader->register();

// Add nodes to the graph
$graph = new PhpFlo\Graph("linecount");
$graph->addNode("Read File", "ReadFile");
$graph->addNode("Split by Lines", "SplitStr");
$graph->addNode("Count Lines", "Counter");
$graph->addNode("Display", "Output");

// Add connections between nodes
$graph->addEdge("Read File", "out", "Split by Lines", "in");
$graph->addEdge("Read File", "error", "Display", "in");
$graph->addEdge("Split by Lines", "out", "Count Lines", "in");
$graph->addEdge("Count Lines", "count", "Display", "in");

// Kick-start the process by sending filename to Read File
$graph->addInitial($fileName, "Read File", "source");

// Make the graph "live"
$network = PhpFlo\Network::create($graph);
?>
