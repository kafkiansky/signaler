<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Seld\Signal\SignalHandler;

final class SeldSignalFactory implements SignalFactory
{
    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(array $signals): Signaler
    {
        /** @var array<int, \Closure[]> $listeners */
        $listeners = [];

        foreach ($signals as $signal => $userListener) {
            $listeners[$signal][] = $userListener;

            if (($listener = pcntl_signal_get_handler($signal)) instanceof \Closure) {
                $listeners[$signal][] = $listener;
            }
        }

        $stack = new SignalStack($listeners);

        $logger = $this->logger;

        $handler = SignalHandler::create(array_keys($signals), static function (int $signalNo, string $signalName) use ($logger): void {
            $logger->notice('Signal {name} received.', ['name' => $signalName]);

            SignalStack::capture($signalNo);
        });

        return new SeldSignaler($handler, $stack, $logger);
    }
}
