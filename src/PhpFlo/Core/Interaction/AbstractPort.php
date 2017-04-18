<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Core\Interaction;

use Evenement\EventEmitter;
use PhpFlo\Common\NetworkInterface as Net;
use PhpFlo\Common\SocketInterface;

/**
 * Class AbstractPort
 *
 * @package PhpFlo\Core\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class AbstractPort extends EventEmitter
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes;

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
    public static $datatypes = [
        'all',
        'bang',
        'string',
        'bool',
        'boolean',
        'number',
        'int',
        'integer',
        'object',
        'array',
        'date',
        'function',
    ];

    /**
     * @param string $name
     * @param array $attributes
     */
    public function __construct(string $name, array $attributes)
    {
        $defaultAttributes = [
            'datatype' => 'all',
            'required' => false,
            'cached' => false,
            'addressable' => false,
        ];

        $this->name = $name;
        $this->attributes = array_replace($defaultAttributes, $attributes);
        $this->socket = null;
        $this->from = null;
    }

    /**
     * Compare in and outport datatypes.
     *
     * @param string $fromType
     * @param string $toType
     * @return bool
     */
    public static function isCompatible(string $fromType, string $toType): bool
    {
        switch (true) {
            case (($fromType == $toType) || ($toType == 'all' || $toType == 'bang')):
                $isCompatible = true;
                break;
            case (($fromType == 'int' || $fromType == 'integer') && $toType == 'number'):
                $isCompatible = true;
                break;
            default:
                $isCompatible = false;
        }

        return $isCompatible;
    }

    /**
     * @param SocketInterface $socket
     */
    public function onConnect(SocketInterface $socket)
    {
        $this->emit(Net::CONNECT, [$socket]);
    }

    /**
     * @param SocketInterface $socket
     */
    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', [$socket]);
    }

    public function onDetach()
    {
        $this->emit('detach', [$this->socket]);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        $attribute = null;

        if (array_key_exists($name, $this->attributes)) {
            $attribute = $this->attributes[$name];
        }

        return $attribute;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @param SocketInterface $socket
     */
    protected function attachSocket(SocketInterface $socket)
    {
        $this->emit('attach', [$socket]);

        $this->from = $socket->from();

        $socket->on(Net::CONNECT, [$this, 'onConnect']);
        $socket->on(Net::BEGIN_GROUP, [$this, 'onBeginGroup']);
        $socket->on(Net::DATA, [$this, 'onData']);
        $socket->on(Net::END_GROUP, [$this, 'onEndGroup']);
        $socket->on(Net::DISCONNECT, [$this, 'onDisconnect']);
        $socket->on(Net::DETACH, [$this, 'onDetach']);
        $this->once(Net::SHUTDOWN, [$this, 'onShutdown']);
    }
}
