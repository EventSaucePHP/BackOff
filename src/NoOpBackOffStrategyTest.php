<?php

namespace EventSauce\BackOff;

use LogicException;
use PHPUnit\Framework\TestCase;

class NoOpBackOffStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_nothing(): void
    {
        $backOff = new NoOpBackOffStrategy();
        self::expectNotToPerformAssertions();

        $backOff->backOff(10000, new LogicException('oh no'));
    }
}
