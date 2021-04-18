<?php

namespace EventSauce\BackOff\Jitter;

use function mt_rand;

class FullJitter implements Jitter
{
    public function jitter(int $sleep): int
    {
        return mt_rand(0, $sleep);
    }
}
