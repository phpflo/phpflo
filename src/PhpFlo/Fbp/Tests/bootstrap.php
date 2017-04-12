<?php

$autoloadFile = __DIR__.'/../vendor/autoload.php';

if (!is_file($autoloadFile)) {
    throw new RuntimeException('Could not find autoloader. Did you run "composer install --dev"?');
}

$loader = require_once $autoloadFile;
