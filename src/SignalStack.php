<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler;

final class SignalStack
{
    /**
     * Signal number that was triggered.
     */
    private static ?int $capturedSignal = null;

    /**
     * @psalm-var array<int, \Closure[]>
     */
    private array $listeners;

    /**
     * @psalm-param array<int, \Closure[]> $listeners
     */
    public function __construct(array $listeners)
    {
        $this->listeners = $listeners;
    }

    public static function capture(int $signal): void
    {
        self::$capturedSignal = $signal;
    }

    /**
     * @psalm-param (callable(\Throwable): void)|null $onErrorHandler
     */
    public function callListeners(?callable $onErrorHandler = null): void
    {
        if (self::$capturedSignal !== null) {
            foreach ($this->listeners[self::$capturedSignal] ?? [] as $listener) {
                try {
                    $listener();
                } catch (\Throwable $e) {
                    if ($onErrorHandler !== null) {
                        $onErrorHandler($e);
                    }
                } finally {
                    self::$capturedSignal = null;
                }
            }
        }
    }
}
