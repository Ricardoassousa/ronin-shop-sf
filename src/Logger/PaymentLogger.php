<?php

namespace App\Logger;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for logging payment-related events.
 *
 * This logger handles messages specific to payments, allowing structured logs
 * to be written to the dedicated "payment" channel in Monolog. It supports
 * different log levels and context information for better observability.
 */
class PaymentLogger
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
     * Logs an payment-related message.
     *
     * This method allows flexible logging of any payment event. The caller can
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