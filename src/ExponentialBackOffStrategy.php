<?php

declare(strict_types=1);

namespace EventSauce\BackOff;

use Throwable;

use function call_user_func;
use function usleep;

class ExponentialBackOffStrategy implements BackOffStrategy
{
    private int $initialDelayMs;
    private int $maxDelay;
    private int $maxTries;
    private float $base;

    /*** @var callable */
    private $sleeper;

    public function __construct(
        int $initialDelayMs,
        int $maxTries,
        int $maxDelay = 2500000,
        float $base = 2.0,
        ?callable $sleeper = null
    ){
        $this->initialDelayMs = $initialDelayMs;
        $this->maxDelay = $maxDelay;
        $this->maxTries = $maxTries;
        $this->base = $base;
        $this->sleeper = $sleeper ?: function(int $duration) { usleep($duration); };
    }

    /**
     * @throws Throwable
     */
    public function backOff(int $tries, Throwable $throwable): void
    {
        if ($tries > $this->maxTries) {
            throw $throwable;
        }

        $delay = $this->initialDelayMs * $this->base ** ($tries - 1);
        $delay = (int) min($this->maxDelay, $delay);

        call_user_func($this->sleeper, $delay);
    }
}