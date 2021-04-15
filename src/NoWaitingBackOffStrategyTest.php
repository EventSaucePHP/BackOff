<?php

namespace EventSauce\BackOff;

use LogicException;
use PHPUnit\Framework\TestCase;

class NoWaitingBackOffStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_nothing_when_no_limit_is_provided(): void
    {
        $backOff = new NoWaitingBackOffStrategy();
        self::expectNotToPerformAssertions();

        $backOff->backOff(1000, new LogicException('oh no'));
    }

    /**
     * @test
     */
    public function it_throws_when_over_the_limit(): void
    {
        $backOff = new NoWaitingBackOffStrategy(100);
        $throwable = new LogicException('oh no');

        self::expectExceptionObject($throwable);

        $backOff->backOff(1000, $throwable);
    }

    /**
     * @test
     */
    public function it_does_not_throw_when_under_the_limit(): void
    {
        $backOff = new NoWaitingBackOffStrategy(100);
        self::expectNotToPerformAssertions();

        $backOff->backOff(50, new LogicException('oh no'));
    }
}
