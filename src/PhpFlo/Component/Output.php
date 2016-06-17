<?php
namespace PhpFlo\Component;

use PhpFlo\Component;
use PhpFlo\ArrayPort;

/**
 * Class Output
 *
 * @package PhpFlo\Component
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Output extends Component
{
    public function __construct()
    {
        $this->inPorts['in'] = new ArrayPort();
        $this->inPorts['in']->on('data', [$this, 'displayData']);
    }

    /**
     * @param mixed $data
     */
    public function displayData($data)
    {
        echo "{$data}\n";
    }
}
