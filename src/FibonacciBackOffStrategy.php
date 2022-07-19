<?php

namespace EventSauce\BackOff;

use EventSauce\BackOff\Jitter\FullJitter;
use EventSauce\BackOff\Jitter\Jitter;
use EventSauce\BackOff\Jitter\NoJitter;
use Throwable;

use function call_user_func;
use function min;
use function usleep;

class FibonacciBackOffStrategy implements BackOffStrategy
{
    private int $initialDelayMs;
    private int $maxDelay;
    private int $maxTries;
    /** @var callable */
    private $sleeper;
    private Jitter $jitter;

    public function __construct(
        int $initialDelayMs,
        int $maxTries,
        int $maxDelay = 2500000,
        ?callable $sleeper = null,
        ?Jitter $jitter = null
    )
    {
        $this->initialDelayMs = $initialDelayMs;
        $this->maxDelay = $maxDelay;
        $this->maxTries = $maxTries;
        $this->sleeper = $sleeper ?: function (int $duration) {
            usleep($duration);
        };
        $this->jitter = $jitter ?: new NoJitter();
    }

    public function backOff(int $tries, Throwable $throwable): void
    {
        if ($tries > $this->maxTries) {
            throw $throwable;
        }

        $delay = $this->fibonacci($tries) * $this->initialDelayMs;
        $delay = min($this->maxDelay, $delay);
        $delay = $this->jitter->jitter($delay);
        call_user_func($this->sleeper, $delay);
    }

    protected function fibonacci(int $n): int
    {
        $phi = 1.6180339887499; // (1 + sqrt(5)) / 2;

        return (int) (($phi ** $n - (1 - $phi) ** $n) / sqrt(5));
    }
}
