<?php

namespace EventSauce\BackOff;

use EventSauce\BackOff\Jitter\FullJitter;
use EventSauce\BackOff\Jitter\Jitter;
use EventSauce\BackOff\Jitter\NoJitter;
use Throwable;

use function call_user_func;
use function min;
use function usleep;

class LinearBackOffStrategy implements BackOffStrategy
{
    private int $initialDelayMs;
    private int $maxDelay;
    private int $maxTries;
    /*** @var callable|null */
    private $sleeper;
    private Jitter $jitter;

    public function __construct(
        int $initialDelayMs,
        int $maxTries,
        int $maxDelay = 2500000,
        ?callable $sleeper = null,
        ?Jitter $jitter = null
    ) {
        $this->initialDelayMs = $initialDelayMs;
        $this->maxDelay = $maxDelay;
        $this->maxTries = $maxTries;
        $this->sleeper = $sleeper ?: function (int $duration) {
            usleep($duration);
        };
        $this->jitter = $jitter ?: new NoJitter();
    }

    /**
     * @throws Throwable
     */
    public function backOff(int $tries, Throwable $throwable): void
    {
        if ($tries > $this->maxTries) {
            throw $throwable;
        }

        $delay = $this->initialDelayMs * $tries;
        $delay = min($this->maxDelay, $delay);
        $delay = $this->jitter->jitter($delay);

        call_user_func($this->sleeper, $delay);
    }
}
