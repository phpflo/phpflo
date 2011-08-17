<?php
namespace NoFlo;

interface SocketInterface
{
    public function getId();

    public function connect();

    public function send();

    public function disconnect();

    public function isConnected();
}
