<?php

namespace EventSauce\BackOff\Jitter;

class NoJitter implements Jitter
{
    public function jitter(int $sleep): int
    {
        return $sleep;
    }
}
