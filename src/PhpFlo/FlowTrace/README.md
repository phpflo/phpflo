# [READONLY] phpflo-flowtrace
[![Build Status](https://travis-ci.org/phpflo/phpflo-flowtrace.svg?branch=master)](https://travis-ci.org/phpflo/phpflo-flowtrace)
[![Code Coverage](https://scrutinizer-ci.com/g/phpflo/phpflo-flowtrace/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpflo/phpflo-flowtrace/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpflo/phpflo-flowtrace/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpflo/phpflo-flowtrace/?branch=master)
[![License](http://img.shields.io/:license-mit-blue.svg)](http://doge.mit-license.org)

Log phpflo network execution for debugging or analysis.

## Tracing events

Every time you want to debug your flows, you need to see the data transitions between your components, the connects/disconnect etc.

With this library you are now able to do so.

## Setup

It's nearly as simple as using phpflo itself.
Add a ```composer require phpflo/flowtrace``` and initialise like this:

```php
<?php
require __DIR__ . '/../../vendor/autoload.php';

$traceableNetwork = new \PhpFlo\FlowTrace\TraceableNetwork(
    new PhpFlo\Core\Network(
        new PhpFlo\Core\Builder\ComponentFactory()
    ),
    new \PhpFlo\FlowTrace\Logger\SimpleFile(__DIR__ . DIRECTORY_SEPARATOR . 'flow.log', 'info')
);
$traceableNetwork
    ->boot(__DIR__.'/count.fbp')
    ->run($fileName, "ReadFile", "source")
    ->shutdown();
```
This will dump all your data flows into a ```flow.log``` where you can later review.
As you might have noticed, the logger has a "level" given, which is PSR3 compatible - in fact, the whole SimpleFile logger is just a basic implementation of the PSR3 AbstractLogger.
You can easily replace this logger with your own PSR compatible one. Providing a certain level will give you more detailed information. "debug" will also give you all connects/disconnects, "info" will just provide data flows and data.

## Logs
The logs are compatible with [flowbased/flowtrace](https://github.com/flowbased/flowtrace) and reproduce flows within Flowhub.

Example of reading the count.fbp file (info):
```log
-> source ReadFile  DATA examples/linecount/count.fbp
ReadFile out -> in SplitbyLines  DATA ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
ReadFile(ReadFile) error -> in Display(Output)
SplitbyLines(SplitStr) out -> in CountLines(Counter)
CountLines(Counter) count -> in Display(Output)

SplitbyLines out -> in CountLines  DATA ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
SplitbyLines out -> in CountLines  DATA ReadFile(ReadFile) error -> in Display(Output)
SplitbyLines out -> in CountLines  DATA SplitbyLines(SplitStr) out -> in CountLines(Counter)
SplitbyLines out -> in CountLines  DATA CountLines(Counter) count -> in Display(Output)
SplitbyLines out -> in CountLines  DATA 
CountLines count -> in Display  DATA i:5;
```

Example (debug):

```log
-> source ReadFile CONN
-> source ReadFile DATA examples/linecount/count.fbp
 ReadFile out -> in SplitbyLines CONN
 ReadFile out -> in SplitbyLines DATA ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
ReadFile(ReadFile) error -> in Display(Output)
SplitbyLines(SplitStr) out -> in CountLines(Counter)
CountLines(Counter) count -> in Display(Output)

 ReadFile out -> in SplitbyLines DISC
 SplitbyLines out -> in CountLines CONN
 SplitbyLines out -> in CountLines DATA ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
 SplitbyLines out -> in CountLines DATA ReadFile(ReadFile) error -> in Display(Output)
 SplitbyLines out -> in CountLines DATA SplitbyLines(SplitStr) out -> in CountLines(Counter)
 SplitbyLines out -> in CountLines DATA CountLines(Counter) count -> in Display(Output)
 SplitbyLines out -> in CountLines DATA 
 SplitbyLines out -> in CountLines DISC
 CountLines count -> in Display CONN
 CountLines count -> in Display DATA i:5;
 CountLines count -> in Display DISC
-> source ReadFile DISC
```
