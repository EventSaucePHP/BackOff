<?php

namespace EventSauce\BackOff;

use Throwable;

/**
 * In your tests, use the NoOp backoff strategy to reduce the time
 * spent waiting on retries.
 */
class NoWaitingBackOffStrategy implements BackOffStrategy
{
    private int $maxTries;

    public function __construct(int $maxTries = -1)
    {
        $this->maxTries = $maxTries;
    }

    public function backOff(int $tries, Throwable $throwable): void
    {
        if ($this->maxTries !== -1 && $tries > $this->maxTries) {
            throw $throwable;
        }
    }
}
