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

/**
 * Class Counter
 *
 * @package PhpFlo\Component
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Counter extends Component
{
    /**
     * @var null
     */
    private $count;

    public function __construct()
    {
        $this->inPorts()->add('in', ['datatype' => 'bang']);
        $this->outPorts()->add('count', ['datatype' => 'int']);

        $this->inPorts()->in->on('data', [$this, 'appendCount']);
        $this->inPorts()->in->on('disconnect', [$this, 'sendCount']);

        $this->count = null;
    }

    /**
     * @param int|null $data
     */
    public function appendCount($data)
    {
        if (is_null($this->count)) {
            $this->count = 0;
        }
        $this->count++;
    }

    public function sendCount()
    {
        $this->outPorts()->count->send($this->count);
        $this->outPorts()->count->disconnect();
        $this->count = null;
    }
}
