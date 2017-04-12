<?php
/*
 * This file is part of the phpflo\phpflo-fbp package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Common;

/**
 * Class FbpDefinitionsInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface FbpDefinitionsInterface
{
    const SOURCE_TARGET_SEPARATOR = '->';
    const PROCESS_DEFINITION = '((?P<inport>[a-zA-Z_]+(\[(?P<inport_no>[0-9]+)\])?)\s)?((?P<process>[\w\/]+)(\((?P<component>[\w\/\\\.]+)?\))?)(\s(?P<outport>[a-zA-Z_]+(\[(?P<outport_no>[0-9]+)\])?))?';
    const NEWLINES = '$\R?^';
    const FILE_LINEFEED = "\n";
    const TARGET_LABEL = 'tgt';
    const SOURCE_LABEL = 'src';
    const COMPONENT_LABEL = 'component';
    const METADATA_LABEL = 'metadata';
    const PROCESSES_LABEL = 'processes';
    const PROCESS_LABEL = 'process';
    const CONNECTIONS_LABEL = 'connections';
    const INITIALIZERS_LABEL = 'initializers';
    const PROPERTIES_LABEL = 'properties';
    const PORT_LABEL = 'port';
    const INPORT_LABEL = 'inport';
    const OUTPORT_LABEL = 'outport';
    const DATA_LABEL = 'data';
}
