PhpFlo: Flow-based programming for PHP 5.3+
==============================================

PhpFlo is a simple [flow-based programming](http://en.wikipedia.org/wiki/Flow-based_programming) implementation for PHP 5.3+. It is a PHP port of [NoFlo](https://github.com/bergie/noflo), a similar tool for Node.js. From WikiPedia:

> In computer science, flow-based programming (FBP) is a programming paradigm that defines applications as networks of "black box" processes, which exchange data across predefined connections by message passing, where the connections are specified externally to the processes. These black box processes can be reconnected endlessly to form different applications without having to be changed internally. FBP is thus naturally component-oriented.

Developers used to the [Unix philosophy](http://en.wikipedia.org/wiki/Unix_philosophy) should be immediately familiar with FBP:

> This is the Unix philosophy: Write programs that do one thing and do it well. Write programs to work together. Write programs to handle text streams, because that is a universal interface.

It also fits well in Alan Kay's [original idea of object-oriented programming](http://userpage.fu-berlin.de/~ram/pub/pub_jf47ht81Ht/doc_kay_oop_en):

> I thought of objects being like biological cells and/or individual computers on a network, only able to communicate with messages (so messaging came at the very beginning -- it took a while to see how to do messaging in a programming language efficiently enough to be useful).

The system has been heavily inspired by [J. Paul Morrison's](http://www.jpaulmorrison.com/) book [Flow-Based Programming](http://www.jpaulmorrison.com/fbp/#More). 

PhpFlo is still quite experimental, but may be useful for implementing flow control in PHP applications.

## Installing

### Installing with Composer

PhpFlo can be installed from [Packagist.org](http://packagist.org/view/PhpFlo/PhpFlo) with the [composer](https://github.com/composer/composer) package manager. Just ensure your `composer.json` has the following:

    {
        "require": {
            "PhpFlo/PhpFlo": ">=0.0.2"
        }
    }

and run:

    $ php composer.phar install

### Installing with Git

PhpFlo requires the [Evenement](https://github.com/igorw/Evenement) event handling library. To get it, check out this repository, and then run:

    $ git submodule init
    $ git submodule update

### Installing with Composer

PhpFlo can also be installed with Composer:

    $ wget http://getcomposer.org/composer.phar 
    $ php composer.phar install

## Autoloading

To use PhpFlo, you need a [PHP Standards Group -compatible autoloader](http://groups.google.com/group/php-standards/web/psr-0-final-proposal). This repository includes Symfony's version of it, and you can use it by:

    <?php
    require_once __DIR__.'/vendor/symfony/Component/ClassLoader/UniversalClassLoader.php';

    $loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
    $loader->registerNamespace('PhpFlo', __DIR__.'/src');
    $loader->registerNamespace('Evenement', __DIR__.'/vendor/Evenement/src');
    $loader->register();
    ?>

## Running the examples

File line count using _embedded_ PhpFlo:

    $ ./examples/linecount/count.php somefile.txt

## Terminology

* Component: individual, pluggable and reusable piece of software. In this case a PHP class implementing `PhpFlo\ComponentInterface`
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

    <?php
    use PhpFlo\Component;
    use PhpFlo\Port;

    class Forwarder extends Component
    {
        protected $description = "This component receives data on a single input port and sends the same data out to the output port";

        public function __construct()
        {
            // Register ports
            $this->inPorts['in'] = new Port();
            $this->outPorts['out'] = new Port();

            // Forward data when we receive it
            $this->inPorts['in']->on('data', array($this, 'forward'));

            // Disconnect output port when input port disconnects
            $this->inPorts['in']->on('disconnect', array($this, 'disconnect'));
        }

        public function forward($data)
        {
            $this->outPorts['out']->send($data);
        }

        public function disconnect()
        {
            $this->outPorts['out']->disconnect();
        }
    }

This example component register two ports: _in_ and _out_. When it receives data in the _in_ port, it opens the _out_ port and sends the same data there. When the _in_ connection closes, it will also close the _out_ connection. So basically this component would be a simple repeater.

You can find more examples of components in the `src/PhpFlo/Components` folder.

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

In addition to using PhpFlo in _embedded mode_ where you create the FBP graph programmatically (see [example](https://github.com/bergie/phpflo/blob/master/examples/linecount/count.php)), you can also initialize and run graphs defined using a JSON file.

The PhpFlo JSON files declare the processes used in the FBP graph, and the connections between them. The file format is shared between PhpFlo and NoFlo, and looks like the following:

    {
        "properties": {
            "name": "Count lines in a file"
        },
        "processes": {
            "Read File": {
                "component": "ReadFile"
            },
            "Split by Lines": {
                "component": "SplitStr"
            },
            ...
        },
        "connections": [
            {
                "data": "README.md",
                "tgt": {
                    "process": "Read File",
                    "port": "source"
                }
            },
            {
                "src": {
                    "process": "Read File",
                    "port": "out"
                },
                "tgt": {
                    "process": "Split by Lines",
                    "port": "in"
                }
            },
            ...
        ]
    }

To run a graph file, load it via the PhpFlow API:

    $network = PhpFlo\Network::loadFile('example.json');

Note that after this the graph is _live_, meaning that you can add and remove nodes and connections, or send new _initial data_ to it. See [example](https://github.com/bergie/phpflo/blob/master/examples/linecount/countFromJson.php).

## Development

PhpFlo development happens on GitHub. Just fork the [main repository](https://github.com/bergie/phpflo), make modifications and send a pull request.

To run the unit tests you need PHPUnit. Run the tests with:

    $ phpunit

### Some ideas

* Use [phpDaemon](http://phpdaemon.net/) to make the network run asynchronously, Node.js -like
