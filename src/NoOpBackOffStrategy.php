<?php

namespace EventSauce\BackOff;

use Throwable;

class NoOpBackOffStrategy implements BackOffStrategy
{
    public function backOff(int $tries, Throwable $throwable): void
    {
        // noop
    }
}
