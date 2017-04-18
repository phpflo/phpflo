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

namespace PhpFlo\Fbp;

use PhpFlo\Common\DefinitionInterface;
use PhpFlo\Common\FbpDefinitionsInterface;
use PhpFlo\Common\Exception\ParserDefinitionException;
use PhpFlo\Common\Exception\ParserException;

/**
 * Class FbpParser
 *
 * @package PhpFlo\Fbp
 * @author Marc Aschmann <maschmann@gmail.com>
 */
final class FbpParser implements FbpDefinitionsInterface
{

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var array
     */
    private $schema;

    /**
     * @var int
     */
    private $linecount;

    /**
     * @var int
     */
    private $linecountOverall;

    /**
     * @var array
     */
    private $definition;

    /**
     * FbpParser constructor.
     *
     * @param string $source optional for initializing
     * @param array $settings optional settings for parser
     */
    public function __construct(string $source = '', array $settings = [])
    {
        $this->source   = $source;
        $this->settings = array_replace_recursive(
            [],
            $settings
        );

        $this->schema = [
            self::PROPERTIES_LABEL => [
                'name' => '',
            ],
            self::INITIALIZERS_LABEL => [],
            self::PROCESSES_LABEL => [],
            self::CONNECTIONS_LABEL => [],
        ];

        $this->definition = [];
    }

    /**
     * @param string $source
     * @return DefinitionInterface
     * @throws ParserException
     */
    public function run(string $source = ''): DefinitionInterface
    {
        if ('' != $source) {
            $this->source = $source;
        }

        if (empty($this->source)) {
            throw new ParserException("FbpParser::run(): no source data or empty string given!");
        }

        $this->definition = $this->schema; // reset
        $this->linecount = 1;
        $this->linecountOverall = 0;

        /*
         * split by lines, OS-independent
         * work each line and parse for definitions
         */
        foreach (preg_split('/' . self::NEWLINES . '/m', $this->source) as $line) {
            $this->linecountOverall++;
            // skip lines if empty or comments
            if ($this->doSkip($line)) {
                continue;
            }
            $subset = $this->examineSubset($line);
            $this->validate($subset, $line); // post-parse validation, easier that way
            $this->definition[self::CONNECTIONS_LABEL] = array_merge_recursive(
                $this->definition[self::CONNECTIONS_LABEL], $subset
            );

            $this->linecount;
        }

        return new FbpDefinition($this->definition);
    }

    /**
     * @param string $line
     * @return array
     * @throws ParserDefinitionException
     */
    private function examineSubset(string $line): array
    {
        $subset = [];
        $step = [];
        $nextSrc = null;
        $hasInitializer = false;

        if (1 == $this->linecount && 0 === strpos(trim($line), "'")) {
            $hasInitializer = true;
        }

        // subset
        foreach (explode(self::SOURCE_TARGET_SEPARATOR, $line) as $definition) {
            $resolved = [];

            if (!$hasInitializer) {
                $resolved = $this->examineDefinition($definition);
            }

            $hasInport = $this->hasValue($resolved, self::INPORT_LABEL);
            $hasOutport = $this->hasValue($resolved, self::OUTPORT_LABEL);

            //define states
            switch (true) {
                case !empty($step[self::DATA_LABEL]) && ($hasInport && $hasOutport):
                    // initializer + inport
                    $nextSrc = $resolved;
                    $step[self::TARGET_LABEL] = $this->addPort($resolved, self::INPORT_LABEL);
                    // multi def oneliner initializer resolved
                    array_push($this->definition[self::INITIALIZERS_LABEL], $step);
                    $step = [];
                    break;
                case !empty($nextSrc) && ($hasInport && $hasOutport):
                    // if there was an initializer, we get a full touple with this iteration
                    $step = [
                        self::SOURCE_LABEL => $this->addPort($nextSrc, self::OUTPORT_LABEL),
                        self::TARGET_LABEL => $this->addPort($resolved, self::INPORT_LABEL),
                    ];
                    $nextSrc = $resolved;
                    array_push($subset, $step);
                    $step = [];
                    break;
                case $hasInport && $hasOutport:
                    // tgt + multi def
                    $nextSrc = $resolved;
                    $step[self::TARGET_LABEL] = $this->addPort($resolved, self::INPORT_LABEL);
                    // check if we've already got the touple ready
                    if (!empty($step[self::SOURCE_LABEL])) {
                        array_push($subset, $step);
                        $step = [];
                    }
                    break;
                case $hasInport && $nextSrc:
                    // use previous OUT as src to fill touple
                    $step[self::SOURCE_LABEL] = $this->addPort($nextSrc, self::OUTPORT_LABEL);
                    $nextSrc = null;
                case $hasInport:
                    $step[self::TARGET_LABEL] = $this->addPort($resolved, self::INPORT_LABEL);
                    // resolved touple
                    if (empty($step[self::DATA_LABEL])) {
                        array_push($subset, $step);
                    } else {
                        array_push($this->definition[self::INITIALIZERS_LABEL], $step);
                    }
                    $nextSrc = null;
                    $step = [];
                    break;
                case $hasOutport:
                    // simplest case OUT -> IN
                    $step[self::SOURCE_LABEL] = $this->addPort($resolved, self::OUTPORT_LABEL);
                    break;
                case $hasInitializer:
                    // initialization value: at the moment we only support one
                    $step[self::DATA_LABEL] = trim($definition, " '");
                    $hasInitializer = false; // reset
                    break;
                default:
                    throw new ParserDefinitionException(
                        "Line ({$this->linecountOverall}) {$line} does not contain in or out ports!"
                    );
            }
        }

        return $subset;
    }

    /**
     * Check if array has a specific key and is not empty.
     *
     * @param array $check
     * @param string $value
     * @return bool
     */
    private function hasValue(array $check, string $value): bool
    {
        if (empty($check[$value])) {
            return false;
        }

        return true;
    }

    /**
     * @param array $definition
     * @param string $label
     * @return array
     */
    private function addPort(array $definition, string $label): array
    {
        return [
            self::PROCESS_LABEL => $definition[self::PROCESS_LABEL],
            self::PORT_LABEL => $definition[$label],
        ];
    }

    /**
     * @param string $line
     * @return array
     * @throws ParserDefinitionException
     */
    private function examineDefinition(string $line): array
    {
        preg_match('/' . self::PROCESS_DEFINITION . '/', $line, $matches);
        foreach ($matches as $key => $value) {
            if (is_numeric($key)) {
                unset($matches[$key]);
            }
        }

        if (!empty($matches[self::PROCESS_LABEL])) {
            if (empty($matches[self::COMPONENT_LABEL])) {
                $matches[self::COMPONENT_LABEL] = $matches[self::PROCESS_LABEL];
            }

            $this->examineProcess($matches);
        } else {
            throw new ParserDefinitionException(
                "No process definition found in line ({$this->linecountOverall}) {$line}"
            );
        }

        return $matches;
    }

    /**
     * Add entry to processes.
     *
     * @param array $process
     */
    private function examineProcess(array $process)
    {
        if (!isset($this->definition[self::PROCESSES_LABEL][$process[self::PROCESS_LABEL]])) {
            $component = $process[self::COMPONENT_LABEL];
            if (empty($component)) {
                $component = $process[self::PROCESS_LABEL];
            }

            $this->definition[self::PROCESSES_LABEL][$process[self::PROCESS_LABEL]] = [
                self::COMPONENT_LABEL => $component,
                self::METADATA_LABEL => [
                    'label' => $component,
                ],
            ];
        }
    }

    /**
     * Add name to definition
     *
     * @param string $line
     */
    private function addName(string $line)
    {
        $this->definition[self::PROPERTIES_LABEL]['name'] = trim(str_replace('#', '', $line));
    }

    /**
     * Check if line is empty or has comment.
     * In case of comments, add name to definition.
     *
     * @param string $line
     * @return bool
     */
    private function doSkip(string $line): bool
    {
        switch (true) {
            case (empty(trim($line))):
                // empty line
                $skip = true;
                break;
            case (1 == preg_match('/(#[\s\w]+)/', $line)):
                if (1 === $this->linecountOverall) {
                    $this->addName($line);
                }
                $skip = true;
                break;
            default:
                $skip = false;
        }

        return $skip;
    }

    /**
     * @param array $subset
     * @param string $line
     */
    private function validate(array $subset, string $line)
    {
        foreach ($subset as $touple) {
            if (empty($touple[self::SOURCE_LABEL])) {
                $this->validationError($line, self::SOURCE_LABEL);
            }

            if (empty($touple[self::TARGET_LABEL])) {
                $this->validationError($line, self::TARGET_LABEL);
            }
        }
    }

    /**
     * @param string $line
     * @param string $port
     * @throws ParserException
     */
    private function validationError(string $line, string $port)
    {
        throw new ParserException(
            "Error on line ({$this->linecountOverall}) {$line}: There is no {$port} defined. Maybe you forgot an in or out port?"
        );
    }
}
