<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo;

use Evenement\EventEmitter;
use PhpFlo\Exception\InvalidTypeException;

/**
 * Class AbstractPort
 *
 * @package PhpFlo
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class AbstractPort extends EventEmitter
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var null
     */
    protected $socket;

    /**
     * @var null
     */
    protected $from;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->socket = null;
        $this->socket = null;
        $this->attributes['_default'] = [
            'datatype' => 'all',
            'required' => false,
            'cached' => false,
        ];
    }

    /**
     * Registers a port event.
     *
     * Possible attributes are datatype, cached and required
     *
     * @param string $event
     * @param callable $listener
     * @param array $attributes
     */
    public function on($event, callable $listener, array $attributes = [])
    {
        // attributes per event,
        $this->attributes[$event] = array_replace(
            $this->attributes['_default'],
            $attributes
        );

        parent::on($event, $listener);
    }

    /**
     * @param SocketInterface $socket
     */
    protected function attachSocket(SocketInterface $socket)
    {
        $this->emit('attach', [$socket]);

        $this->from = $socket->from;

        $socket->on('connect', [$this, 'onConnect']);
        $socket->on('beginGroup', [$this, 'onBeginGroup']);
        $socket->on('data', [$this, 'onData']);
        $socket->on('endGroup', [$this, 'onEndGroup']);
        $socket->on('disconnect', [$this, 'onDisconnect']);
    }

    /**
     * @param SocketInterface $socket
     */
    public function onConnect(SocketInterface $socket)
    {
        $this->emit('connect', [$socket]);
    }

    /**
     * @param SocketInterface $socket
     */
    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', [$socket]);
    }

    /**
     * Analyze data for configured type.
     *
     * As of this moment, due to the possibilities of PHP,
     * supported types are
     *
     * all: the port can deal with any data type
     * bang: the port doesn't do anything with the contents of a data packet, only with the fact that a packet was sent
     * string
     * boolean/bool
     * number
     * int/integer
     * object
     * array
     * date
     * function
     *
     * @param string $event
     * @param mixed $data
     * @return bool
     * @throws InvalidTypeException
     */
    protected function hasType($event, $data)
    {
        $hasType = false;
        $type = $this->attributes[$event]['datatype'];

        switch ($type) {
            case 'all':
            case 'bang':
                $hasType = true;
                break;
            case 'bool':
            case 'boolean':
                if (!is_null($data) && is_bool($data)) {
                    $hasType = true;
                }
                break;
            case 'number':
                if (!is_null($data) &&
                    (is_int($data) || is_float($data) || is_numeric($data))
                ) {
                    $hasType = true;
                }
                break;
            case 'int':
            case 'integer':
                if (!is_null($data) && is_int($data)) {
                    $hasType = true;
                }
                break;
            case 'object':
                if (!is_null($data) && is_object($data)) {
                    $hasType = true;
                }
                break;
            case 'array':
                if (!is_null($data) && is_array($data)) {
                    $hasType = true;
                }
                break;
            case 'date':
                if (!is_null($data) && is_a('\DateTime', $data)) {
                    $hasType = true;
                }
                break;
            case 'function':
                if (!is_null($data) && is_callable($data)) {
                    $hasType = true;
                }
                break;
            default:
                throw new InvalidTypeException(
                    $type . ' is not a valid in/out port data type for ' . $event . '.'
                );
        }

        return $hasType;
    }
}
