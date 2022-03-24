<?php

declare(strict_types=1);

namespace Kafkiansky\Signaler;

interface Signaler
{
    public function isTriggered(): bool;
}
