<?php
/*
 * This file is part of the phpflo/flowtrace package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\FlowTrace\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class SimpleFile
 *
 * @package PhpFlo\Logger
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class SimpleFile extends AbstractLogger implements LoggerInterface
{
    const DEFAULT_FILENAME = 'flow.log';

    /**
     * @var string
     */
    private $logFile;

    /**
     * @var array
     */
    private $levels;

    /**
     * @param string $logFile path/filename to log to.
     * @param string $level PSR3 loglevel to log
     */
    public function __construct(string $logFile, string $level = LogLevel::INFO)
    {
        $log = pathinfo($logFile);
        // check if $logFile is a dir because of possible stream url
        $isLogdir = is_dir($logFile);
        if (!$isLogdir && (isset($log['dirname']) && !is_dir($log['dirname']))) {
            throw new \InvalidArgumentException(
                "Directory does not exist: {$log['dirname']}"
            );
        }

        if ($isLogdir || !isset($log['filename'])) {
            $logFile = $logFile . DIRECTORY_SEPARATOR . self::DEFAULT_FILENAME;
        }
        $this->logFile = $logFile;

        $this->prepareLogLevels($level);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if (in_array($level, $this->levels)) {
            file_put_contents(
                $this->logFile, $message . PHP_EOL, FILE_APPEND
            );
        }
    }

    /**
     * Prepare log levels for logfile
     *
     * @param string $level
     */
    private function prepareLogLevels(string $level)
    {
        $levels = [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];

        $key = array_search($level, $levels);

        if (null !== $key) {
            $this->levels = array_slice($levels, 0, $key + 1);
        }
    }
}
