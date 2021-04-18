<?php

namespace EventSauce\BackOff\Jitter;

use PHPUnit\Framework\TestCase;

use function array_sum;
use function count;
use function max;
use function min;

class HalfJitterTest extends TestCase
{
    /**
     * @test
     */
    public function jitting_results_in_random_values_across_a_half_the_range(): void
    {
        $jitter = new HalfJitter();
        $jittedSleeps = [];

        for ($i = 0; $i < 100000; $i++) {
            $jittedSleeps[] = $jitter->jitter(1000);
        }

        $average = (int) (array_sum($jittedSleeps) / count($jittedSleeps));
        $max = max(...$jittedSleeps);
        $min = min(...$jittedSleeps);

        self::assertTrue($average > 700);
        self::assertTrue($average < 800);
        self::assertTrue($max > 950);
        self::assertTrue($max <= 1000);
        self::assertTrue($min < 550);
        self::assertTrue($min >= 500);
    }
}
