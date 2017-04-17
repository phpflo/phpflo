# [READONLY] phpflo-fbp: load, parse, dump
Flowbased programming protocol (FBP) config file loader, using the FBP domain specific language (DSL).

[![Build Status](https://travis-ci.org/phpflo/phpflo-fbp.svg?branch=master)](https://travis-ci.org/phpflo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpflo/phpflo-fbp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpflo/phpflo-fbp/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/phpflo/phpflo-fbp/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpflo/phpflo-fbp/?branch=master)
[![License](http://img.shields.io/:license-mit-blue.svg)](http://doge.mit-license.org)


## Introduction

This library allows you to load and parse configuration for your phpflo project. It also works standalone if you want to convert your old JSON configs to FBP spec.
Supported config formats are JSON (.json), YAML (.yml) and FBP (.fbp), output is an object of type FbpDefinition. This allows you to output your parsed content in different formats, ranging from array over FBP, JSON to YAML.

## Code Samples

Basic usage:
```php
// load FBP config
$defintiion = PhpFlo\Loader\Loader::load('my/fbp/config/file.fbp');
```
You can load JSON, YAML and FBP that way.

Parser by itself:
```php
$myFbpConfig = <<<EOF
# Read a file and cout its lines
'test.file' -> IN ReadFile(ReadFile)
ReadFile(ReadFile) OUT -> IN SplitbyLines(SplitStr)
ReadFile() ERROR -> IN Display(Output)
SplitbyLines() OUT -> IN CountLines(Counter)
CountLines() COUNT -> IN Display()
EOF;

$parser = new PhpFlo\Fbp\FbpParser();
$definition = $parser->run($myFbpConfig);
```
Dump your flow to a format:
```php
$json = PhpFlo\Fbp\FbpDumper::toJson($definition);
$yaml = PhpFlo\Fbp\FbpDumper::toYaml($definition);
$fbp = PhpFlo\Fbp\FbpDumper::toFbp($definition);
```

The definition has following schema:
```php
$schema = [
    'properties' => ['name' => '',],
    'initializers' => [
        [
            'data' => '',
            'tgt' => [
                'process' => '',
                'port' => '',
            ],
        ],
    ],
    'processes' => [
        'ReadFile' => [
            'component' => '',
            'metadata' => [
                'label' => '',
            ],
        ],
    ],
    'connections' => [
        [
            'src' => [
                'process' => '',
                'port' => '',
            ],
            'tgt' => [
                'process' => '',
                'port' => '',
            ],
        ],
    ],
]
```
### FBP DSL defintions

If you want to write definition files, here are the rules:

*General syntax*:
```
// <process_alias>(<optional_process_name>) <port><[optional_port_number]> -> <port><[optional_port_number]> <process_alias>(<optional_process_name>)
// examples
ReadFile(ReadFile) OUT -> IN SplitbyLines(SplitStr)
ReadFile() OUT -> IN SplitbyLines()
ReadFile() out[1] -> In[3] SplitbyLines()
```
* All elements are _case sensitive_
* The parentheses at the end of a process definition are mandatory (even if empty): ```<process>()```
* Process names are w+
* Port names can be [a-zA-Z_]
* Each line determines a new chain of events, meaning at least two processes with two connecting ports, separated by a " -> " like ```<process>() <port> -> <port> <process>()```
* Otherwise there is a ```<initializer> -> <port> <process>()```

For better understanding, the whole RegEx used for definition examination is:
```
((?P<inport>[a-zA-Z_]+(\[(?P<inport_no>[0-9]+)\])?)\s)?((?P<process>[\w\/]+)(\((?P<component>[\w\/\\\.]+)?\))?)(\s(?P<outport>[a-zA-Z_]+(\[(?P<outport_no>[0-9]+)\])?))?
```
*Comments:*
You can add comments and empty lines for better readability and documentation. If you have a comment in the first line, it will be used as name of the definition.
```
# this definition reads files and counts their lines
ReadFile() OUT -> IN SplitbyLines()
ReadFile() out -> In SplitbyLines()

# some comment

# and for readability :-)
ReadFile(ReadFile) OUT -> IN SplitbyLines(SplitStr)
```

*Initializer:*
You can have initial values for a graph:
```
'test.file' -> IN ReadFile()
```

*Multiple definitions*:
You can have a complete chain of definitions in one line to enhance visibility of a chain of events:
```
GreetUser() DATA -> OPTIONS Render() OUT -> STRING WriteResponse()
```

## Installation

Regular install via composer:
```php
composer require phpflo/phpflo-fbp
```
