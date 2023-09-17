<?php

declare(strict_types=1);

namespace EventSauce\BackOff;

use Throwable;

/**
 * # This one is for the lazy people.
 *
 * This class is introduced for extremely lazy developers that can't even be bothered
 * to write the tiniest amount of bootstrapping code, ever. LAZY! YOU'RE LAZY! BOOOO!
 */
class BackOffRunner
{
    private BackOffStrategy $strategy;

    /**
     * @var class-string<Throwable>
     */
    private string $retryOn;

    /**
     * @param class-string<Throwable> $retryOn
     */
    public function __construct(BackOffStrategy $strategy, string $retryOn = Throwable::class)
    {
        $this->strategy = $strategy;
        $this->retryOn = $retryOn;
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function run(callable $task)
    {
        $tries = 0;

        while(true) {
            try {
                return $task();
            } catch (Throwable $exception) {
                if ( ! $exception instanceof $this->retryOn) {
                    throw $exception;
                }

                $this->strategy->backOff(++$tries, $exception);
            }
        }
    }
}