<?php

namespace EventSauce\BackOff;

/**
 * @codeCoverageIgnore
 */
final class SleepSpy
{
    private static array $sleeps = [];

    private function __construct() {}

    public static function reset(): void
    {
        self::$sleeps = [];
    }

    public static function recordedSleeps(): array
    {
        return self::$sleeps;
    }

    public static function sleep(int $sleep): void
    {
        self::$sleeps[] = $sleep;
    }

}
