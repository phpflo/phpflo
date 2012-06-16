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

// Load network from graph file
$network = PhpFlo\Network::loadFile(__DIR__.'/count.json');

// Kick-start the process by sending filename
$network->getGraph()->addInitial($fileName, "ReadFile", "source");
?>
