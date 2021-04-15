<?php

declare(strict_types=1);

namespace EventSauce\BackOff;

use Throwable;

class CappedExponentialBackOffStrategy implements BackOffStrategy
{
    private int $initialDelayMs;
    private int $maxDelay;
    private int $maxTries;
    private float $exponent;

    public function __construct(
        int $initialDelayMs,
        int $maxDelay = 2500000,
        int $maxTries = -1,
        float $exponent = 2.0
    ){
        $this->initialDelayMs = $initialDelayMs;
        $this->maxDelay = $maxDelay;
        $this->maxTries = $maxTries;
        $this->exponent = $exponent;
    }

    /**
     * @throws Throwable
     */
    public function backOff(int $tries, Throwable $throwable): void
    {
        if ($this->hasExhaustedTries($tries)) {
            throw $throwable;
        }

        $delay = $this->initialDelayMs * $this->exponent ** ($tries - 1);

        if ($this->maxDelay !== -1) {
            $delay = min($this->maxDelay, $delay);
        }

        usleep((int) $delay);
    }

    private function hasExhaustedTries(int $tries): bool
    {
        return $this->maxTries !== -1 && $tries >= $this->maxTries;
    }
}
