<?php

namespace App\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for logging stock-related events.
 *
 * This logger handles messages specific to stock, allowing structured logs
 * to be written to the dedicated "stock" channel in Monolog. It supports
 * different log levels and context information for better observability.
 */
class StockLogger
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
     * Logs an stock-related message.
     *
     * This method allows flexible logging of any stock event. The caller can
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