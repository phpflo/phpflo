<?php
/*
 * This file is part of the phpflo/phpflo-flowtrace package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\FlowTrace;

use PhpFlo\Common\AbstractNetworkDecorator;
use PhpFlo\Common\NetworkInterface;
use Psr\Log\LoggerInterface;

/**
 * Network for tracing events.
 *
 * @package PhpFlo\FlowTrace
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class TraceableNetwork extends AbstractNetworkDecorator implements NetworkInterface
{
    const TYPE_DATA = 'data';
    const TYPE_CONNECT = 'connect';
    const TYPE_DISCONNECT = 'disconnect';
    const TYPE_BEGIN_GROUP = 'begin.group';
    const TYPE_END_GROUP = 'end.group';

    /**
     * @var array
     */
    private $actions;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TraceableNetwork constructor.
     *
     * @param NetworkInterface $network
     * @param LoggerInterface $logger
     */
    public function __construct(NetworkInterface $network, LoggerInterface $logger)
    {
        parent::__construct($network);

        $this->actions = [
            self::TYPE_DATA       => 'DATA',
            self::TYPE_CONNECT    => 'CONN',
            self::TYPE_DISCONNECT => 'DISC'
        ];

        $this->logger = $logger;
        $this->network->hook(self::TYPE_DATA, 'flowtrace', $this->trace(self::TYPE_DATA));
        $this->network->hook(self::TYPE_CONNECT, 'flowtrace', $this->trace(self::TYPE_CONNECT));
        $this->network->hook(self::TYPE_DISCONNECT, 'flowtrace', $this->trace(self::TYPE_DISCONNECT));
    }

    /**
     * Wrap the creation of the callback
     *
     * @param string $type
     * @return \Closure
     */
    private function trace(string $type) : \Closure
    {
        $trace = function() use ($type) {
            switch ($type) {
                case TraceableNetwork::TYPE_DATA:
                    $this->traceData(func_get_args(), $type);
                    break;
                case TraceableNetwork::TYPE_CONNECT:
                case TraceableNetwork::TYPE_DISCONNECT:
                    $this->traceAction(func_get_args(), $type);
                    break;
            }
        };

        return $trace->bindTo($this);
    }

    /**
     * @param array $args
     * @param string $type
     */
    private function traceData(array $args, string $type)
    {
        $data   = $args[0];
        $socket = $args[1];

        if (!is_string($data)) {
            $data = serialize($data);
        }
        $to = $socket->to();
        $message = "-> {$to['port']} {$to['process']['id']}";

        $from = $socket->from();
        if (isset($from['process'])) {
            $message = " {$from['process']['id']} {$from['port']} {$message}";
        }

        $this->logger->info("{$message} {$this->actions[$type]} {$data}");
    }

    /**
     * @param array $args
     * @param string $type
     */
    private function traceAction(array $args, string $type)
    {
        $socket = $args[0];
        $to = $socket->to();
        $message = "-> {$to['port']} {$to['process']['id']}";

        $from = $socket->from();
        if (isset($from['process'])) {
            $message = " {$from['process']['id']} {$from['port']} {$message}";
        }

        $this->logger->debug("{$message} {$this->actions[$type]}");
    }
}
