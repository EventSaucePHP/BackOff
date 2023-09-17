<?php
declare(strict_types=1);

namespace EventSauce\BackOff\Jitter;

class JitterAfterThreshold implements Jitter
{
    private int $threshold;
    private Jitter $strategy;

    public function __construct(
        int $threshold,
        Jitter $strategy = null
    ) {

        $this->threshold = $threshold;
        $this->strategy = $strategy ?: new FullJitter();
    }

    public function jitter(int $sleep): int
    {
        if ($sleep <= $this->threshold) {
            return $sleep;
        }

        return $this->threshold + $this->strategy->jitter($sleep - $this->threshold);
    }
}