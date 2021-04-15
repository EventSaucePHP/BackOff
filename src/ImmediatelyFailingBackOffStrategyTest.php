<?php

namespace EventSauce\BackOff;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImmediatelyFailingBackOffStrategyTest extends TestCase
{
    /**
     * @test
     */
    public function it_always_fails(): void
    {
        $backOff = new ImmediatelyFailingBackOffStrategy();
        $exception = new RuntimeException('oh no');

        self::expectExceptionObject($exception);

        $backOff->backOff(1, $exception);
    }
}
