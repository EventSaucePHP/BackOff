<?php

namespace EventSauce\BackOff;

use Closure;
use EventSauce\BackOff\Jitter\NoJitter;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use const PHP_INT_MAX;

class LinearBackOffStrategyTest extends TestCase
{
    private ?int $recordedSleep = null;
    private Closure $sleeper;

    protected function setUp(): void
    {
        $this->recordedSleep = null;
        $this->sleeper = function(int $duration) {
            $this->recordedSleep = $duration;
        };
    }

    /**
     * @test
     * @dataProvider dpExpectedSleeps
     */
    public function it_backs_of_exponentially(int $tries, int $expectedSleep): void
    {
        $backoff = new LinearBackOffStrategy(100, 25, 250000, $this->sleeper, new NoJitter());
        $backoff->backOff($tries, new RuntimeException('oops'));

        self::assertEquals($expectedSleep, $this->recordedSleep);
    }

    public static function dpExpectedSleeps(): iterable
    {
        return [
            /** tries, expected sleep ms */
            [1, 100],
            [2, 200],
            [3, 300],
            [4, 400],
            [5, 500],
            [6, 600],
            [7, 700],
        ];
    }

    /**
     * @test
     * @dataProvider dpExpectedCappedSleeps
     */
    public function it_respects_a_max_sleep_time(int $tries, int $expectedSleep): void
    {
        $backoff = new LinearBackOffStrategy(100, 25, 600, $this->sleeper, new NoJitter());
        $backoff->backOff($tries, new RuntimeException('oops'));

        self::assertEquals($expectedSleep, $this->recordedSleep);
    }

    public static function dpExpectedCappedSleeps(): iterable
    {
        return [
            /** tries, expected sleep ms */
            [1, 100],
            [2, 200],
            [3, 300],
            [4, 400],
            [5, 500],
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
        $backoff = new LinearBackOffStrategy(0, $maxTries);
        $exception = new RuntimeException('oops');

        self::expectExceptionObject($exception);

        $backoff->backOff($tries, $exception);
    }

    public static function dpGoingOverTheMaxTries(): iterable
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
     */
    public function it_does_not_throw_an_exception_when_max_tries_is_infinite(): void
    {
        $this->expectNotToPerformAssertions();

        $backoff = new LinearBackOffStrategy(0, -1);
        $exception = new RuntimeException('oops');

        $backoff->backOff(PHP_INT_MAX, $exception);
    }

    /**
     * @test
     */
    public function it_does_not_throw_when_at_the_max_tries(): void
    {
        $backoff = new LinearBackOffStrategy(100, 100, 100, $this->sleeper, new NoJitter());
        $exception = new RuntimeException('oops');

        self::expectNotToPerformAssertions();

        $backoff->backOff(100, $exception);
    }
}
