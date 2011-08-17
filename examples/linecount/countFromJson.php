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

// Load network from graph file
$network = PhpFlo\Network::loadFile(__DIR__.'/count.json');

// Kick-start the process by sending filename
$network->getGraph()->addInitial($fileName, "ReadFile", "source");
?>
