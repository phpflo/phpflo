PhpFlo: Flow-based programming for PHP
==============================================

[![Build Status](https://secure.travis-ci.org/phpflo/phpflo.png)](http://travis-ci.org/phpflo/phpflo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpflo/phpflo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpflo/phpflo/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/phpflo/phpflo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phpflo/phpflo/?branch=master)
[![License](http://img.shields.io/:license-mit-blue.svg)](http://doge.mit-license.org)

PhpFlo is a simple [flow-based programming](http://en.wikipedia.org/wiki/Flow-based_programming) implementation for PHP. It is a PHP port of [NoFlo](https://noflojs.org), a similar tool for Node.js. From WikiPedia:

> In computer science, flow-based programming (FBP) is a programming paradigm that defines applications as networks of "black box" processes, which exchange data across predefined connections by message passing, where the connections are specified externally to the processes. These black box processes can be reconnected endlessly to form different applications without having to be changed internally. FBP is thus naturally component-oriented.

Developers used to the [Unix philosophy](http://en.wikipedia.org/wiki/Unix_philosophy) should be immediately familiar with FBP:

> This is the Unix philosophy: Write programs that do one thing and do it well. Write programs to work together. Write programs to handle text streams, because that is a universal interface.

It also fits well in Alan Kay's [original idea of object-oriented programming](http://userpage.fu-berlin.de/~ram/pub/pub_jf47ht81Ht/doc_kay_oop_en):

> I thought of objects being like biological cells and/or individual computers on a network, only able to communicate with messages (so messaging came at the very beginning -- it took a while to see how to do messaging in a programming language efficiently enough to be useful).

The system has been heavily inspired by [J. Paul Morrison's](http://www.jpaulmorrison.com/) book [Flow-Based Programming](http://www.jpaulmorrison.com/fbp/#More).

PhpFlo is still quite experimental, but may be useful for implementing flow control in PHP applications.

## Installing

PhpFlo can be installed from [Packagist.org](http://packagist.org/view/PhpFlo/PhpFlo) with the [composer](https://github.com/composer/composer) package manager. Just ensure your `composer.json` has the following:

```sh
php composer.phar require phpflo/phpflo
```

This gives you phpflo, common, fbp and flowtrace packages. 

## Autoloading

To use PhpFlo, you need a [PHP Standards Group -compatible autoloader](http://groups.google.com/group/php-standards/web/psr-0-final-proposal). You can use the Composer-supplied autoloader:

```php
<?php

require 'vendor/autoload.php';
```

## Examples

You can find examples on how to use phpflo in the [phpflo-component](https://github.com/phpflo/phpflo-component) package.

## Terminology

* Component: individual, pluggable and reusable piece of software. In this case a PHP class implementing `PhpFlo\Common\ComponentInterface`
* Graph: the control logic of a FBP application, can be either in programmatical or file format
* Inport: inbound port of a component
* Network: collection of processes connected by sockets. A running version of a graph
* Outport: outbound port of a component
* Process: an instance of a component that is running as part of a graph

## Components

A component is the main ingredient of flow-based programming. Component is a PHP class providing a set of input and output port handlers. These ports are used for connecting components to each other.

PhpFlo processes (the boxes of a flow graph) are instances of a component, with the graph controlling connections between ports of components.

### Structure of a component

Functionality a component provides:

* List of inports (named inbound ports)
* List of outports (named outbound ports)
* Handler for component initialization that accepts configuration
* Handler for connections for each inport

A minimal component would look like the following:

```php
<?php

use PhpFlo\Core\ComponentTrait;
use PhpFlo\Common\ComponentInterface;

class Forwarder implements ComponentInterface
{
    use ComponentTrait;
    protected $description = "This component receives data on a single input port and sends the same data out to the output port";

    public function __construct()
    {
        // Register ports
        $this->inPorts()->add('in', ['datatype' => 'all']);
        $this->outPorts()->add('out', ['datatype' => 'all']);

        // Forward data when we receive it
        $this->inPorts()->in->on('data', array($this, 'forward'));

        // Disconnect output port when input port disconnects
        $this->inPorts()->in->on('disconnect', array($this, 'disconnect'));
    }

    public function forward($data)
    {
        $this->outPorts()->out->send($data);
    }

    public function disconnect()
    {
        $this->outPorts->out->disconnect();
    }
}
```

Alternatively you can use ```PhpFlo\Core\Component``` via direct inheritance, which internally uses the trait.
This example component register two ports: _in_ and _out_. When it receives data in the _in_ port, it opens the _out_ port and sends the same data there. When the _in_ connection closes, it will also close the _out_ connection. So basically this component would be a simple repeater.
You can find more examples of components in the [phpflo-compoent](https://github.com/phpflo/phpflo-component) package.
Please mind that there's an mandatory second parameter for the "add" command. This array receives the port's meta information and has following defaults:
 
``` php
    $defaultAttributes = [
        'datatype' => 'all',
        'required' => false,
        'cached' => false,
        'addressable' => false,
    ];
```
This is but a subset of the available attributes, a noflo port can take.

* _datatype_ defines the "to be expected" datatype of the dataflow. Currently _all_ datatypes from noflo are implemented
* _required_ not implemented yet
* _cached_ not implemented yet
* _addressable_ decides if a port needs to be either an instance of Port (false) or ArrayPort (true)

Defining the datatype is mandatory, since there is a port matching check during graph building, according to this matrix:

| out\in   | all      | bang     | string   | bool     | number   | int      | object   | array    | date     | function |
| -------- |:--------:|:--------:|:--------:|:--------:|:--------:|:--------:|:--------:|:--------:|:--------:| --------:|
| all      |    x     |    x     |          |          |          |          |          |          |          |          |
| bang     |    x     |    x     |          |          |          |          |          |          |          |          |
| string   |    x     |    x     |    x     |          |          |          |          |          |          |          |
| bool     |    x     |    x     |          |    x     |          |          |          |          |          |          |
| number   |    x     |    x     |          |          |    x     |          |          |          |          |          |
| int      |    x     |    x     |          |          |    x     |    x     |          |          |          |          |
| object   |    x     |    x     |          |          |          |          |    x     |          |          |          |
| array    |    x     |    x     |          |          |          |          |          |    x     |          |          |
| date     |    x     |    x     |          |          |          |          |          |          |    x     |          |
| function |    x     |    x     |          |          |          |          |          |          |          |    x     |

These types are only implicitly checked. There is no data validation during runtime!

### Some words on component design

Components should aim to be reusable, to do one thing and do it well. This is why often it is a good idea to split functionality traditionally done in one function to multiple components. For example, counting lines in a text file could happen in the following way:

* Filename is sent to a _Read File_ component
* _Read File_ reads it and sends the contents onwards to _Split String_ component
* _Split String_ splits the contents by newlines, and sends each line separately to a _Count_ component
* _Count_ counts the number of packets it received, and sends the total to a _Output_ component
* _Output_ displays the number

This way the whole logic of the application is in the graph, in how the components are wired together. And each of the components is easily reusable for other purposes.

If a component requires configuration, the good approach is to set sensible defaults in the component, and to allow them to be overridden via an input port. This method of configuration allows the settings to be kept in the graph itself, or for example to be read from a file or database, depending on the needs of the application.

The components should not depend on a particular global state, either, but instead attempt to keep the input and output ports their sole interface to the external world. There may be some exceptions, like a component that listens for HTTP requests or Redis pub-sub messages, but even in these cases the server, or subscription should be set up by the component itself.

### Ports and events

Being a flow-based programming environment, the main action in PhpFlo happens through ports and their connections. There are five events that can be associated with ports:

* _Attach_: there is a connection to the port
* _Connect_: the port has started sending or receiving a data transmission
* _Data_: an individual data packet in a transmission. There might be multiple depending on how a component operates
* _Disconnect_: end of data transmission
* _Detach_: A connection to the port has been removed

It depends on the nature of the component how these events may be handled. Most typical components do operations on a whole transmission, meaning that they should wait for the _disconnect_ event on inports before they act, but some components can also act on single _data_ packets coming in.

When a port has no connections, meaning that it was initialized without a connection, or a _detach_ event has happened, it should do no operations regarding that port.

## Graph file format

In addition to using PhpFlo in _embedded mode_ where you create the FBP graph programmatically (see [example](https://github.com/phpflo/phpflo/blob/master/examples/linecount/count.php)), you can also initialize and run graphs defined using a FBP file.
This format gives you the advantage of much less definition work, compared to the deprecated (but still valid) JSON files.

If you have older JSON definitions, you can still use them or convert then to FBP, using the dumper wrapped by the graph or directly from definition:
```php
$builder = new \PhpFlo\Core\Builder\ComponentFactory();
$network = new PhpFlo\Core\Network($builder);
$network->boot(__DIR__.'/count.json', $builder);
file_put_contents('./count.fbp', $network->getGraph()->toFbp());
```

The PhpFlo FBP files declare the processes used in the FBP graph, and the connections between them. The file format is shared between PhpFlo and NoFlo, and looks like the following:

```
ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
ReadFile(ReadFile) error -> in Display(Output)
SplitbyLines(SplitStr) out -> in CountLines(Counter)
CountLines(Counter) count -> in Display(Output)
```
Other supported formats are YAML and JSON, but keep in mind that the FBP domain specific language (DSL) should be the way to go if you want to use something like the noflo ui.

JSON example:
```json
{
    "properties": {
        "name": "Count lines in a file"
    },
    "processes": {
        "ReadFile": {
            "component": "ReadFile"
        },
        "SplitbyLines": {
            "component": "SplitStr"
        },
        "CountLines": {
            "component": "Counter"
        },
        "Display": {
            "component": "Output"
        }
    },
    "connections": [
        {
            "src": {
                "process": "ReadFile",
                "port": "out"
            },
            "tgt": {
                "process": "SplitbyLines",
                "port": "in"
            }
        },
        {
            "src": {
                "process": "ReadFile",
                "port": "error"
            },
            "tgt": {
                "process": "Display",
                "port": "in"
            }
        },
        {
            "src": {
                "process": "SplitbyLines",
                "port": "out"
            },
            "tgt": {
                "process": "CountLines",
                "port": "in"
            }
        },
        {
            "src": {
                "process": "CountLines",
                "port": "count"
            },
            "tgt": {
                "process": "Display",
                "port": "in"
            }
        }
    ]
}
```

YAML example:
```yaml
properties:
    name: 'Count lines in a file'
initializers: {  }
processes:
    ReadFile:
        component: ReadFile
        metadata: { label: ReadFile }
    SplitbyLines:
        component: SplitStr
        metadata: { label: SplitStr }
    Display:
        component: Output
        metadata: { label: Output }
    CountLines:
        component: Counter
        metadata: { label: Counter }
connections:
    -
        src: { process: ReadFile, port: OUT }
        tgt: { process: SplitbyLines, port: IN }
    -
        src: { process: ReadFile, port: ERROR }
        tgt: { process: Display, port: IN }
    -
        src: { process: SplitbyLines, port: OUT }
        tgt: { process: CountLines, port: IN }
    -
        src: { process: CountLines, port: COUNT }
        tgt: { process: Display, port: IN }
```

To run a graph file, load it via the PhpFlow API:

```php
<?php

$builder = new PhpFlo\Core\Builder\ComponentFactory();

// create network
$network = new PhpFlo\Core\Network($builder);
$network
    ->boot(__DIR__.'/count.fbp')
    ->run($fileName, "ReadFile", "source")
    ->shutdown();
```

Note that after this the graph is _live_, meaning that you can add and remove nodes and connections, or send new _initial data_ to it. See [example](https://github.com/phpflo/phpflo/blob/master/examples/linecount/countFromJson.php).

Since the network now also features the ```HookableInterface```, you can easily add callbacks on events for e.g. debugging purposes:

```php
<?php
$builder = new PhpFlo\Core\Builder\ComponentFactory();

// create network
$network = new PhpFlo\Core\Network($builder);
$network
    ->hook(
        'data',
        'trace',
        function ($data, $socket) {
            echo $socket->getId() . print_r($data, true) . "\n";
        }
    )
    ->boot(__DIR__.'/count.fbp')
    ->run($fileName, "ReadFile", "source")
    ->shutdown();
```

As you can see, there's a lot of potential in the callbacks, since they can also use object references to store and/or manipulate data, but natively receive socket and data from the supported events.
This feature is also used in the upcoming [phpflo/flowtrace](https://github.com/phpflo/flowtrace) library, which decorates the ```Network``` class and adds PSR-3 compatible logging.

## Testing

To be able to test your components, two traits are provided in [phpflo-core](https://github.com/phpflo/phpflo-core) which is automatically included as a dependency for phpflo. 
```PhpFlo\Test\ComponentTestTrait``` and ```PhpFlo\Test\Stub\Trait``` contain the necessary tools to make testing easier.

```php
<?php
namespace Tests\PhpFlo\Component;

use PhpFlo\Component\Counter;
use PhpFlo\Test\ComponentTestHelperTrait;
use PhpFlo\Test\StubTrait;

class CounterTest extends \PHPUnit_Framework_TestCase
{
    use StubTrait;
    use ComponentTestHelperTrait;

    public function testBehavior()
    {
        $counter = new Counter();
        $this->connectPorts($counter);

        $this->assertTrue($counter->inPorts()->has('in'));
        $this->assertTrue($counter->outPorts()->has('count'));

        $counter->appendCount(1);
        $counter->appendCount("2");
        $counter->appendCount(null);

        $counter->sendCount();

        $countData = $this->getOutPortData('count');
        $this->assertEquals(3, $countData[0]);
    }
}

```

Within this code example you can see that the outPorts are available via ```getOutportData('alias'')``` method. This will always return an array of data sent to that specific port, because you can iteratively call ports within a component.
On codelevel this is nothing more than callbacks with an internal storage of your data, so you can test a component and its interaction in isolation.
To be able to use the component in testing you will first need to ```connectPorts($component)``` or separately ```connectInPorts($component); connectOutPorts($component)```, so phpflo won't throw any "port not connected" errors at you.

## Development

PhpFlo development happens on GitHub. Just fork the [main repository](https://github.com/phpflo/phpflo), make modifications and send a pull request.

To run the unit tests you need PHPUnit. Run the tests with in development:

```sh
$ bin/phpunit
```

### Some ideas

* Use [phpDaemon](http://daemon.io/) to make the network run asynchronously, Node.js -like
