<?php

namespace EventSauce\BackOff\Jitter;

use function mt_rand;

class ScatteredJitter implements Jitter
{
    private float $range;

    public function __construct(float $range)
    {
        $this->range = $range;
    }

    public function jitter(int $sleep): int
    {
        $jittered = (int) ($sleep * $this->range);
        $base = $sleep - $jittered;

        return $base + mt_rand(0, $jittered * 2);
    }
}
