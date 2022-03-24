<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler\Test;

use Kafkiansky\Signaler\SeldSignalFactory;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor, MixedArgument, MixedArrayAssignment
 */
final class SignalerTest extends TestCase
{
    public function testListenerWasTriggered(): void
    {
        $factory = new SeldSignalFactory();

        /** @var string[] $calls */
        $calls = [];

        $signaler = $factory->subscribe([
            \SIGINT => function () use (&$calls): void {
                $calls[] = 'FIRST';
            },
            \SIGTERM => function() use (&$calls): void {
                $calls[] = 'SECOND';
            }
        ]);

        self::assertFalse($signaler->isTriggered());
        \posix_kill(\posix_getpid(), SIGINT);
        self::assertTrue($signaler->isTriggered());
        self::assertCount(1, $calls);
    }

    public function testPreviousListenerWasAlsoTriggered(): void
    {
        /** @var string[] $calls */
        $calls = [];

        pcntl_signal(\SIGINT, function () use (&$calls): void {
            $calls[] = 'FIRST';
        });

        $factory = new SeldSignalFactory();

        $signaler = $factory->subscribe([
            \SIGINT => function () use (&$calls): void {
                $calls[] = 'SECOND';
            },
            \SIGTERM => function() use (&$calls): void {
                $calls[] = 'THIRD';
            }
        ]);

        self::assertFalse($signaler->isTriggered());
        \posix_kill(\posix_getpid(), SIGINT);
        self::assertTrue($signaler->isTriggered());
        self::assertCount(2, $calls);
        self::assertEquals('SECOND', \array_shift($calls));
        self::assertEquals('FIRST', \array_shift($calls));
    }
}
