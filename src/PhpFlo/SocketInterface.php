<?php
namespace PhpFlo;

interface SocketInterface
{
    public function getId();

    public function connect();

    public function send($data);

    public function disconnect();

    public function isConnected();
}
