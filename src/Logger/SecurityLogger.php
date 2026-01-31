<?php

namespace App\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for logging security-related events.
 *
 * This logger handles messages specific to security, allowing structured logs
 * to be written to the dedicated "security" channel in Monolog. It supports
 * different log levels and context information for better observability.
 */
class SecurityLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs an security-related message.
     *
     * This method allows flexible logging of any security event. The caller can
     * specify the log message, optional context, and the severity level.
     *
     * @param string $message
     * @param array $context
     * @param string $level
     */
    public function log(string $message, array $context = [], string $level = LogLevel::INFO): void
    {
        $this->logger->log($level, $message, $context);
    }

}