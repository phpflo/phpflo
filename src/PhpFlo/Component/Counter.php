<?php
namespace PhpFlo\Component;

use PhpFlo\Component;
use PhpFlo\Port;

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
    private $count = null;

    public function __construct()
    {
        $this->inPorts['in'] = new Port();
        $this->outPorts['count'] = new Port();

        $this->inPorts['in']->on('data', [$this, 'appendCount']);
        $this->inPorts['in']->on('disconnect', [$this, 'sendCount']);
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
        $this->outPorts['count']->send($this->count);
        $this->outPorts['count']->disconnect();
        $this->count = null;
    }
}
