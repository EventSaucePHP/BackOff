<?php
declare(strict_types=1);

namespace EventSauce\BackOff\Jitter;

use PHPUnit\Framework\TestCase;
use function array_sum;
use function count;
use function max;
use function min;

class JitterAfterThresholdTest extends TestCase
{
    /**
     * @test
     */
    public function jitting_results_in_random_values_across_a_full_range_but_not_below_threshold(): void
    {
        $jitter = new JitterAfterThreshold(250);
        $jittedSleeps = [];

        for ($i = 0; $i < 100000; $i++) {
            $jittedSleeps[] = $jitter->jitter(1000);
        }

        $average = (int) (array_sum($jittedSleeps) / count($jittedSleeps));
        $max = max(...$jittedSleeps);
        $min = min(...$jittedSleeps);

        self::assertTrue($average > 525);
        self::assertTrue($average < 725);
        self::assertTrue($max > 900);
        self::assertTrue($max <= 1000);
        self::assertTrue($min < 350);
        self::assertTrue($min >= 250);
    }
}