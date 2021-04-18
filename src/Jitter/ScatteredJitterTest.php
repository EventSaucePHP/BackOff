<?php

namespace EventSauce\BackOff\Jitter;

use PHPUnit\Framework\TestCase;

use function array_sum;
use function count;
use function max;
use function min;

class ScatteredJitterTest extends TestCase
{
    /**
     * @test
     */
    public function it_jitters_with_half(): void
    {
        $jitter = new ScatteredJitter(0.5);
        $jittedSleeps = [];

        for ($i = 0; $i < 100000; $i++) {
            $jittedSleeps[] = $jitter->jitter(1000);
        }

        $average = (int) (array_sum($jittedSleeps) / count($jittedSleeps));
        $max = max(...$jittedSleeps);
        $min = min(...$jittedSleeps);

        self::assertTrue($average > 900);
        self::assertTrue($average < 1100);
        self::assertTrue($max > 1400);
        self::assertTrue($max <= 1500);
        self::assertTrue($min < 550);
        self::assertTrue($min >= 500);
    }

    /**
     * @test
     */
    public function it_jitters_with_a_quarter(): void
    {
        $jitter = new ScatteredJitter(0.25);
        $jittedSleeps = [];

        for ($i = 0; $i < 100000; $i++) {
            $jittedSleeps[] = $jitter->jitter(1000);
        }

        $average = (int) (array_sum($jittedSleeps) / count($jittedSleeps));
        $max = max(...$jittedSleeps);
        $min = min(...$jittedSleeps);

        self::assertTrue($average > 950);
        self::assertTrue($average < 1150);
        self::assertTrue($max > 1200);
        self::assertTrue($max <= 1250);
        self::assertTrue($min < 800);
        self::assertTrue($min >= 750);
    }
}
