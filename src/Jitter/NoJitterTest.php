<?php

namespace EventSauce\BackOff\Jitter;

use PHPUnit\Framework\TestCase;

class NoJitterTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_not_jitter_sleeps(): void
    {
        $jitter = new NoJitter();

        for ($i = 0; $i < 1000; $i++) {
            $sleep = $jitter->jitter($i);
            self::assertEquals($i, $sleep);
        }
    }
}
