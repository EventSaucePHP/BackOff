<?php

namespace EventSauce\BackOff;

use Throwable;

class ImmediatelyFailingBackOffStrategy implements BackOffStrategy
{
    public function backOff(int $tries, Throwable $throwable): void
    {
        throw $throwable;
    }
}
