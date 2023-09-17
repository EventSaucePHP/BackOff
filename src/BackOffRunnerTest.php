<?php

declare(strict_types=1);

namespace EventSauce\BackOff;

use Exception;
use LogicException;
use PHPUnit\Framework\TestCase;
use Throwable;

class BackOffRunnerTest extends TestCase
{
    /**
     * @test
     */
    public function running_a_callable_with_backoff(): void
    {
        $runner = new BackOffRunner(new NoWaitingBackOffStrategy(5));
        $tries = 0;
        $exception = null;

        try {
            $runner->run(function () use (&$tries) {
                $tries++;
                throw new Exception('oh no, this is terrible');
            });
        } catch (Throwable $throwable) {
            $exception = $throwable;
        }

        self::assertEquals(6, $tries); // retry 5 failures + 1 final fail
        self::assertEquals(new Exception('oh no, this is terrible'), $exception);
    }
    /**
     * @test
     */
    public function not_matching_the_exception_type(): void
    {
        $runner = new BackOffRunner(new NoWaitingBackOffStrategy(5), LogicException::class);
        $tries = 0;
        $exception = null;

        try {
            $runner->run(function () use (&$tries) {
                $tries++;
                throw new Exception('oh no, this is terrible');
            });
        } catch (Throwable $throwable) {
            $exception = $throwable;
        }

        self::assertEquals(1, $tries); // retry 5 failures + 1 final fail
        self::assertEquals(new Exception('oh no, this is terrible'), $exception);
    }
}