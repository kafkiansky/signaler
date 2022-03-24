<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler;

interface SignalFactory
{
    /**
     * @param array<int, \Closure> $signals
     */
    public function subscribe(array $signals): Signaler;
}
