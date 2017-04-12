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

// create network
$traceableNetwork = new \PhpFlo\TraceableNetwork(
    new PhpFlo\Network(
        new PhpFlo\Builder\ComponentFactory()
    ),
    new \PhpFlo\Logger\SimpleFile(__DIR__ . DIRECTORY_SEPARATOR . 'flow.log', 'info')
);
$traceableNetwork
    ->boot(__DIR__.'/count.fbp')
    ->run($fileName, "ReadFile", "source")
    ->shutdown();
