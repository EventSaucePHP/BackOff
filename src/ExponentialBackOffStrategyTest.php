<?php

namespace EventSauce\BackOff;

use Closure;
use EventSauce\BackOff\Jitter\NoJitter;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExponentialBackOffStrategyTest extends TestCase
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
        $backoff = new ExponentialBackOffStrategy(100, 25, 250000, 2.0, $this->sleeper, new NoJitter());
        $backoff->backOff($tries, new RuntimeException('oops'));

        self::assertEquals($expectedSleep, $this->recordedSleep);
    }

    public static function dpExpectedSleeps(): iterable
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
        $backoff = new ExponentialBackOffStrategy(100, 25, 600, 2.0, $this->sleeper, new NoJitter());
        $backoff->backOff($tries, new RuntimeException('oops'));

        self::assertEquals($expectedSleep, $this->recordedSleep);
    }

    public static function dpExpectedCappedSleeps(): iterable
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
        $backoff = new ExponentialBackOffStrategy(0, $maxTries);
        $exception = new RuntimeException('oops');
        $backoff->backOff(1, new LogicException('not this'));

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
    public function it_does_not_throw_when_at_the_max_tries(): void
    {
        $backoff = new ExponentialBackOffStrategy(100, 100, 100, 2.0, $this->sleeper, new NoJitter());
        $exception = new RuntimeException('oops');

        self::expectNotToPerformAssertions();

        $backoff->backOff(100, $exception);
    }

    /**
     * @test
     * @dataProvider dpExpectedSleepFromExponent
     */
    public function it_uses_a_specified_exponent_value(int $tries, float $exponent, int $expectedSleep): void
    {
        $backoff = new ExponentialBackOffStrategy(100, 100, 1000, $exponent, $this->sleeper, new NoJitter());
        $backoff->backOff($tries, new RuntimeException('oops'));

        self::assertEquals($expectedSleep, $this->recordedSleep);
    }

    public static function dpExpectedSleepFromExponent(): iterable
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
