<?php

namespace EventSauce\BackOff;

use Closure;
use EventSauce\BackOff\Jitter\NoJitter;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FibonacciBackOffStrategyTest extends TestCase
{
    private ?int $recordedSleep = null;
    private Closure $sleeper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordedSleep = null;
        $this->sleeper = function (int $duration) {
            $this->recordedSleep = $duration;
        };
    }

    /**
     * @test
     * @dataProvider dpExpectedSleeps
     */
    public function it_uses_fibonacci_to_calculate_the_delay(int $tries, int $expectedDelay): void
    {
        $backOff = new FibonacciBackOffStrategy(100000, 25, 2500000, $this->sleeper, new NoJitter());

        $backOff->backOff($tries, new LogicException('oh no'));

        self::assertEquals($expectedDelay, $this->recordedSleep);
    }

    public function dpExpectedSleeps(): iterable
    {
        return [
            [1, 100000],
            [2, 100000],
            [3, 200000],
            [4, 300000],
            [5, 500000],
            [6, 800000],
            [7, 1300000],
        ];
    }

    /**
     * @test
     */
    public function it_throws_when_over_max_tries(): void
    {
        $backOff = new FibonacciBackOffStrategy(0, 25);
        $exception = new LogicException('oh no');
        $backOff->backOff(25, new RuntimeException('no this'));

        self::expectExceptionObject($exception);

        $backOff->backOff(26, $exception);
    }
}
