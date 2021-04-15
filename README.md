# EventSauce BackOff

This library provides an interface for encapsulated back-off strategies.

```bash
composer require eventsuace/backoff
```

## Leveraging the back-off strategies

A back-off strategy is applied in side a piece of code that retries a certain task.

```php
<?php

use EventSauce\BackOff\BackOffStrategy;

class BusinessLogic
{
    public function __construct(
    private ExternalDependency $dependency,
    private BackOffStrategy $backOff,
    ) {}

    public function performAction(): void
    {
        $tries = 0;

        start:
        try {
            ++$tries;
            $this->dependency->actionThatMayFail();
        } catch (Throwable $throwable) {
            $this->backOff->backOff($tries, $throwable);
            goto start;
        }
    }
}
```

A well-known back-off strategy is _exponential back-off_, which is the default provided strategy.

```php
<?php

use EventSauce\BackOff\CappedExponentialBackOffStrategy;

$backOff = new CappedExponentialBackOffStrategy(
    100, // initial delay in microseconds
    2500000, // (optional) max delay in microseconds, default 2.5 seconds
    15, // (optional) max number of tries, default -1 (no max),
    2.0, // (optional) base to control the growth factor, default 2.0
);

$businessLogic = new BusinessLogic(new ExternalDependency(), $backOff);

try {
    $businessLogic->performAction();
} catch (Throwable $throwable) {
    // handle the throwable
}
```
