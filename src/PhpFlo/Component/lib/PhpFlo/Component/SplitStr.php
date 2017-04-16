<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Component;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\ComponentTrait;

/**
 * Class SplitStr
 *
 * @package PhpFlo\Component
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class SplitStr implements ComponentInterface
{
    use ComponentTrait;

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
        $this->inPorts()
            ->add('in', ['datatype' => 'string'])
            ->add('delimiter', ['datatype' => 'string']);
        $this->outPorts()->add('out', ['datatype' => 'string']);

        $this->inPorts()->delimiter->on('data', [$this, 'setDelimiter']);
        $this->inPorts()->in->on('data', [$this, 'appendString']);
        $this->inPorts()->in->on('disconnect', [$this, 'splitString']);

        $this->delimiterString = "\n";
        $this->string = "";
    }

    /**
     * @param string $data
     */
    public function setDelimiter(string $data)
    {
        $this->delimiterString = $data;
    }

    /**
     * @param string $data
     */
    public function appendString(string $data)
    {
        $this->string .= $data;
    }

    public function splitString()
    {
        $parts = explode($this->delimiterString, $this->string);
        foreach ($parts as $part) {
            $this->outPorts()->out->send($part);
        }
        $this->outPorts()->out->disconnect();
        $this->string = "";
    }
}
