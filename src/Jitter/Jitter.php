<?php

namespace EventSauce\BackOff\Jitter;

interface Jitter
{
    public function jitter(int $sleep): int;
}
