<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Component;

use PhpFlo\Component;
use PhpFlo\Port;

/**
 * Class SplitStr
 *
 * @package PhpFlo\Component
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class SplitStr extends Component
{
    /**
     * @var string
     */
    private $delimiterString;

    /**
     * @var string
     */
    private $string;

    public function __construct()
    {
        $this->inPorts['in'] = new Port();
        $this->inPorts['delimiter'] = new Port();
        $this->outPorts['out'] = new Port();

        $this->inPorts['delimiter']->on('data', [$this, 'setDelimiter']);
        $this->inPorts['in']->on('data', [$this, 'appendString']);
        $this->inPorts['in']->on('disconnect', [$this, 'splitString']);

        $this->delimiterString = "\n";
        $this->string = "";
    }

    /**
     * @param string $data
     */
    public function setDelimiter($data)
    {
        $this->delimiterString = $data;
    }

    /**
     * @param string $data
     */
    public function appendString($data)
    {
        $this->string .= $data;
    }

    public function splitString()
    {
        $parts = explode($this->delimiterString, $this->string);
        foreach ($parts as $part) {
            $this->outPorts['out']->send($part);
        }
        $this->outPorts['out']->disconnect();
        $this->string = "";
    }
}
