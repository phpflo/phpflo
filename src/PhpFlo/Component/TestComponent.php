<?php
/*
 * This file is part of the <package> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Component;


use PhpFlo\Component;

class TestComponent extends Component
{
    public function __construct()
    {
        $this->inPorts()->add('in', ['datatype' => 'string']);
        $this->inPorts()->in->on('data', [$this, 'checkData']);
    }
}
