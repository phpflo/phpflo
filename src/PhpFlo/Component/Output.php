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
        $this->inPorts()->add('in', ['datatype' => 'int', 'addressable' => true]);
        $this->inPorts()->in->on('data', [$this, 'displayData']);
    }

    /**
     * @param mixed $data
     */
    public function displayData($data)
    {
        echo "{$data}\n";
    }
}
