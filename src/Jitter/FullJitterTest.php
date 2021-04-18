<?php

namespace EventSauce\BackOff\Jitter;

use PHPUnit\Framework\TestCase;

use function array_sum;
use function max;
use function min;
use function var_dump;

class FullJitterTest extends TestCase
{
    /**
     * @test
     */
    public function jitting_results_in_random_values_across_a_full_range(): void
    {
        $jitter = new FullJitter();
        $jittedSleeps = [];

        for ($i = 0; $i < 100000; $i++) {
            $jittedSleeps[] = $jitter->jitter(1000);
        }

        $average = (int) (array_sum($jittedSleeps) / count($jittedSleeps));
        $max = max(...$jittedSleeps);
        $min = min(...$jittedSleeps);

        self::assertTrue($average > 400);
        self::assertTrue($average < 600);
        self::assertTrue($max > 900);
        self::assertTrue($max <= 1000);
        self::assertTrue($min < 100);
        self::assertTrue($min >= 0);
    }
}
