<?php

namespace EventSauce\BackOff;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class CappedExponentialBackOffStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        SleepSpy::reset();
    }

    /**
     * @test
     * @dataProvider dpExpectedSleeps
     */
    public function it_backs_of_exponentially(int $tries, int $expectedSleep): void
    {
        $backoff = new CappedExponentialBackOffStrategy(100, -1);
        $backoff->backOff($tries, new RuntimeException('oops'));

        $sleeps = SleepSpy::recordedSleeps();
        self::assertCount(1, $sleeps);
        self::assertEquals($expectedSleep, $sleeps[0]);
    }

    public function dpExpectedSleeps(): iterable
    {
        return [
            /** tries, expected sleep ms */
            [1, 100],
            [2, 200],
            [3, 400],
            [4, 800],
            [5, 1600],
            [6, 3200],
            [7, 6400],
        ];
    }

    /**
     * @test
     * @dataProvider dpExpectedCappedSleeps
     */
    public function it_respects_a_max_sleep_time(int $tries, int $expectedSleep): void
    {
        $backoff = new CappedExponentialBackOffStrategy(100, 600);
        $backoff->backOff($tries, new RuntimeException('oops'));

        $sleeps = SleepSpy::recordedSleeps();
        self::assertCount(1, $sleeps);
        self::assertEquals($expectedSleep, $sleeps[0]);
    }

    public function dpExpectedCappedSleeps(): iterable
    {
        return [
            /** tries, expected sleep ms */
            [1, 100],
            [2, 200],
            [3, 400],
            [4, 600],
            [5, 600],
            [6, 600],
            [7, 600],
        ];
    }

    /**
     * @test
     * @dataProvider dpGoingOverTheMaxTries
     */
    public function it_throws_an_exception_when_over_the_max_tries(int $maxTries, int $tries): void
    {
        $backoff = new CappedExponentialBackOffStrategy(100, 100, $maxTries);
        $exception = new RuntimeException('oops');

        self::expectExceptionObject($exception);

        $backoff->backOff($tries, $exception);
    }

    public function dpGoingOverTheMaxTries(): iterable
    {
        return [
            /** max tries, tries */
            [10, 11],
            [10, 15],
            [100, 101],
            [100, 150],
        ];
    }

    /**
     * @test
     * @dataProvider dpExpectedSleepFromExponent
     */
    public function it_uses_a_specified_exponent_value(int $tries, float $exponent, int $expectedSleep): void
    {
        $backoff = new CappedExponentialBackOffStrategy(100, -1, -1, $exponent);
        $backoff->backOff($tries, new RuntimeException('oops'));

        $sleeps = SleepSpy::recordedSleeps();
        self::assertCount(1, $sleeps);
        self::assertEquals($expectedSleep, $sleeps[0]);
    }

    public function dpExpectedSleepFromExponent(): iterable
    {
        return [
            /** tries, exponent, expected sleep ms */
            [2, 1.5, 150],
            [3, 1.5, 225],
            [2, 2.5, 250],
            [3, 2.5, 625],
        ];
    }
}
