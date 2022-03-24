<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler\Test;

use Kafkiansky\Signaler\SignalStack;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor, MixedArrayAssignment, MixedArgument
 */
final class SignalStackTest extends TestCase
{
    public function testListenersExecutionOrder(): void
    {
        /** @var string[] $calls */
        $calls = [];

        $stack = new SignalStack([
            \SIGINT => [
                function () use (&$calls): void {
                    $calls[] = 'FIRST';
                },
                function () use (&$calls): void {
                    $calls[] = 'SECOND';
                },
            ],
        ]);

        SignalStack::capture(\SIGINT);

        $stack->callListeners();
        self::assertCount(2, $calls);
        self::assertEquals('FIRST', \array_shift($calls));
        self::assertEquals('SECOND', \array_shift($calls));

        $stack->callListeners();
        self::assertCount(0, $calls);
    }

    public function testNoOneListenerWasCalledIfNoSignalWasTriggered(): void
    {
        /** @var string[] $calls */
        $calls = [];

        $stack = new SignalStack([
            \SIGINT => [
                function () use (&$calls): void {
                    $calls[] = 'trigger';
                },
            ],
        ]);

        $stack->callListeners();
        self::assertCount(0, $calls);
    }

    public function testErrorHandling(): void
    {
        $triggeredError = null;

        /** @var string[] $calls */
        $calls = [];

        $stack = new SignalStack([
            \SIGINT => [
                function (): void {
                    throw new \InvalidArgumentException('Listener error.');
                },
                function () use (&$calls): void {
                    $calls[] = 'SECOND';
                },
            ],
        ]);

        SignalStack::capture(\SIGINT);

        $stack->callListeners(function (\Throwable $e) use (&$triggeredError): void {
            $triggeredError = $e;
        });

        self::assertCount(1, $calls);
        self::assertEquals('SECOND', \array_shift($calls));
        self::assertInstanceOf(\InvalidArgumentException::class, $triggeredError);
        self::assertEquals('Listener error.', $triggeredError->getMessage());
    }
}
