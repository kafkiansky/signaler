<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler;

use Psr\Log\LoggerInterface;
use Seld\Signal\SignalHandler;

final class SeldSignaler implements Signaler
{
    private SignalHandler $handler;
    private SignalStack $signalStack;
    private LoggerInterface $logger;

    public function __construct(SignalHandler $handler, SignalStack $signalStack, LoggerInterface $logger)
    {
        $this->handler = $handler;
        $this->signalStack = $signalStack;
        $this->logger = $logger;
    }

    public function isTriggered(): bool
    {
        if ($this->handler->isTriggered()) {
            $this->signalStack->callListeners(function (\Throwable $e): void {
                $this->logger->critical('The error "{error}" occurred on signal termination.', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            });
        }

        return $this->handler->isTriggered();
    }
}
