# Stack Signaler.

![test](https://github.com/kafkiansky/signaler/workflows/test/badge.svg?event=push)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/kafkiansky/signaler.svg?style=flat-square)](https://packagist.org/packages/kafkiansky/signaler)

### Contents

- [Installation](#installation)
- [Usage](#usage)
- [Testing](#testing)
- [License](#license)

## Installation


```bash
composer require kafkiansky/signaler
```

## Usage

Simple example with `\SIGINT` signal.

```php
use Kafkiansky\Signaler\SeldSignalFactory;
use Psr\Log\NullLogger;

$factory = new SeldSignalFactory(new NullLogger());

$signaler = $factory->subscribe([
    \SIGINT => function () use ($worker): void {
        $worker->stop();
    }
]);

while ($signaler->isTriggered() === false) {
    //
}
```

The main purpose of this library is to prevent the signal listener from being replaced by the `pcntl_signal` function if it was previously configured by vendor code.
The library carefully saves previous signal listeners and will call them after yours.

In e.g.:

```php
use Kafkiansky\Signaler\SeldSignalFactory;
use Psr\Log\NullLogger;

pcntl_signal(\SIGINT, function (): void {
    // This function will still be called after all your listeners.
});

$factory = new SeldSignalFactory(new NullLogger());

$signaler = $factory->subscribe([
    \SIGINT => function () use ($worker): void {
        $worker->stop();
    }
]);

while ($signaler->isTriggered() === false) {
    //
}
```

## Testing

``` bash
$ composer test
```  

## License

The MIT License (MIT). See [License File](LICENSE) for more information.
