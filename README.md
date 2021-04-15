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

## Design rationale

Unlike other exponential back-off libraries, this library doesn't run the
operation you want to retry. This makes the design of the package very
simple. It also doesn't impose any limitations on the surround code.

You can retry based on a return value:

```php
use EventSauce\BackOff\BackOffStrategy;

function action(Client $client, BackOffStrategy $backOff): void
{
    $tries = 0;
    start:
    $tries++;
    $response = $client->doSomething();
    
    if ($response == SomeParticular::VALUE) {
        $backOff->backOff($tries, new LogicException('Exhausted back-off'));
        goto start;
    }
}
```

You can retry on a specific exception type:

```php
use EventSauce\BackOff\BackOffStrategy;

function action(Client $client, BackOffStrategy $backOff): void
{
    $tries = 0;
    start:
    $tries++;
    
    try {
        $client->doSomething();
    } catch (SpecificException $exception) {
        $backOff->backOff($tries, $exception);
        goto start;
    }
}
```

The choice is yours. Enjoy!
