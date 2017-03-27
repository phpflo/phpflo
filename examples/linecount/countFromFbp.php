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

// create network
$network = new PhpFlo\Network($builder);
$network
    ->hook(
        'data',
        'trace',
        function ($data, $socket) {
            echo $socket->getId() . print_r($data, true) . "\n";
        }
    )
    ->boot(__DIR__.'/count.fbp')
    ->run($fileName, "ReadFile", "source");

// re-run the process by sending filename
$network->run($fileName, "ReadFile", "source");
$network->shutdown();
