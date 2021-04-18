<?php

namespace EventSauce\BackOff\Jitter;

use function mt_rand;

class HalfJitter implements Jitter
{
    public function jitter(int $sleep): int
    {
        $half = (int) ($sleep / 2);

        return $half + mt_rand(0, $half);
    }
}
