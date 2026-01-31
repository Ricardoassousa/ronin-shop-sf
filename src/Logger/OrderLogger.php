<?php

namespace App\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for logging order-related events.
 *
 * This logger handles messages specific to orders, allowing structured logs
 * to be written to the dedicated "order" channel in Monolog. It supports
 * different log levels and context information for better observability.
 */
class OrderLogger
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
     * Logs an order-related message.
     *
     * This method allows flexible logging of any order event. The caller can
     * specify the log message, optional context, and the severity level.
     *
     * Example usage:
     * ```php
     * $orderLogger->log('Order created', ['order_id' => 123]);
     * $orderLogger->log('Order failed', ['order_id' => 123], LogLevel::ERROR);
     * ```
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