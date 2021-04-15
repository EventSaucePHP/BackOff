<?php

declare(strict_types=1);

namespace EventSauce\BackOff;

use Throwable;

interface BackOffStrategy
{
    public function backOff(int $tries, Throwable $throwable): void;
}
