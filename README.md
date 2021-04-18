# EventSauce BackOff

This library provides an interface for encapsulated back-off strategies.

```bash
composer require eventsauce/backoff
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

## Exponential back-off

A well-known back-off strategy is _exponential back-off_, which is the default provided strategy.

```text
sleep = initial_delay * (base ^ (number_of_tries - 1)
```

```php
<?php

use EventSauce\BackOff\ExponentialBackOffStrategy;

$backOff = new ExponentialBackOffStrategy(
    100000, // initial delay in microseconds, 0.1 seconds
    15, //  max number of tries
    2500000, // (optional) max delay in microseconds, default 2.5 seconds
    2.0, // (optional) base to control the growth factor, default 2.0
);

$businessLogic = new BusinessLogic(new ExternalDependency(), $backOff);

try {
    $businessLogic->performAction();
} catch (Throwable $throwable) {
    // handle the throwable
}
```

## Fibonacci back-off

The Fibonacci back-off strategy increases the back-off based on the fibonacci sequence.

```text
sleep = initial_delay * fibonacci(number_of_tries)
```

```php
<?php

use EventSauce\BackOff\FibonacciBackOffStrategy;

$backOff = new FibonacciBackOffStrategy(
    100000, // initial delay in microseconds, 0.1 seconds
    15, // max number of tries
    2500000, // (optional) max delay in microseconds, default 2.5 seconds
);

$businessLogic = new BusinessLogic(new ExternalDependency(), $backOff);

try {
    $businessLogic->performAction();
} catch (Throwable $throwable) {
    // handle the throwable
}
```

## Linear back-off

The linear back-off strategy increases the back-off time linearly.

```text
sleep = initial_delay * number_of_tries
```

```php
<?php

use EventSauce\BackOff\LinearBackOffStrategy;

$backOff = new LinearBackOffStrategy(
    100000, // initial delay in microseconds, 0.1 seconds
    15, // max number of tries
    2500000, // (optional) max delay in microseconds, default 2.5 seconds
);

$businessLogic = new BusinessLogic(new ExternalDependency(), $backOff);

try {
    $businessLogic->performAction();
} catch (Throwable $throwable) {
    // handle the throwable
}
```

## Jitter

When many clients are forced to retry, having deterministic interval
can cause many of these clients to retry at the same time. Adding
randomness to the mix ensures retrying clients are scattered across
time. The randomness ensures that it is less likely for the clients
to all retry at the same time.

### Using Jitter

Every strategy that sleeps accepted a `EventSauce\BackOff\Jitter\Jitter`
implementation.

```php
use EventSauce\BackOff\ExponentialBackOffStrategy;
use EventSauce\BackOff\FibonacciBackOffStrategy;
use EventSauce\BackOff\LinearBackOffStrategy;

$exponential = new ExponentialBackOffStrategy(100000, 25, jitter: $jitter);
$fibonacci = new FibonacciBackOffStrategy(100000, 25, jitter: $jitter);
$linear = new LinearBackOffStrategy(100000, 25, jitter: $jitter);
```

### Full Jitter

The full jitter uses a randomized value from 0 to the initial calculated sleep time.

```text
sleep = number_between(0, sleep)
```

```php
use EventSauce\BackOff\Jitter\FullJitter;
$jitter = new FullJitter();
```

### Half Jitter

The full jitter uses a randomized value from half the initial sleep to the full initial sleep time.

```text
sleep = sleep / 2 + number_between(0 , sleep / 2)
```

```php
use EventSauce\BackOff\Jitter\HalfJitter;
$jitter = new HalfJitter();
```

### Scattered Jitter

The scattered jitter uses a range in across which it's scatter the
resulting values. To illustrate, here are a few examples:

| Range | Min | Max
| --- | ---: | ---: |
| 0.25 | 75% | 125% | 
| 0.5 | 50% | 150% | 
| 0.1 | 90% | 110% |


```text

sleep = sleep / 2 + number_between(0 , sleep / 2)
```

```php
use EventSauce\BackOff\Jitter\ScatteredJitter;
$jitter = new ScatteredJitter();
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

---

PS: yes, those were a lot of goto statements, deal with it 😎
