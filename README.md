# Quillstack Middleware

[![Tests](https://github.com/quillstack/middleware/actions/workflows/tests.yml/badge.svg)](https://github.com/quillstack/middleware/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/quillstack/middleware.svg)](https://packagist.org/packages/quillstack/middleware)
[![Downloads](https://img.shields.io/packagist/dt/quillstack/middleware.svg)](https://packagist.org/packages/quillstack/middleware)
[![PHP Version](https://img.shields.io/packagist/php-v/quillstack/middleware)](https://packagist.org/packages/quillstack/middleware)
[![StyleCI](https://github.styleci.io/repos/304422648/shield?branch=main)](https://github.styleci.io/repos/304422648?branch=main)
[![CodeFactor](https://www.codefactor.io/repository/github/quillstack/middleware/badge)](https://www.codefactor.io/repository/github/quillstack/middleware)
[![Quality Gate](https://sonarcloud.io/api/project_badges/measure?project=quillstack_middleware&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=quillstack_middleware)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=quillstack_middleware&metric=coverage)](https://sonarcloud.io/summary/new_code?id=quillstack_middleware)
[![Maintainability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_middleware&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_middleware)
[![Reliability](https://sonarcloud.io/api/project_badges/measure?project=quillstack_middleware&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_middleware)
[![Security](https://sonarcloud.io/api/project_badges/measure?project=quillstack_middleware&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=quillstack_middleware)
[![Maintainability](https://api.codeclimate.com/v1/badges/8605086862df3345be8e/maintainability)](https://codeclimate.com/github/quillstack/middleware/maintainability)
[![License](https://img.shields.io/packagist/l/quillstack/middleware)](https://github.com/quillstack/middleware/blob/main/LICENSE)

The middleware library based on [PSR-15](https://www.php-fig.org/psr/psr-15/). Full
documentation: https://quillstack.org/middleware

A request passes through a stack of middleware on its way in and the response comes back out
through the same stack. Each one decides whether to carry on, and what to do with the answer.

## Why this exists

A PSR-15 stack is a small idea: each layer gets the request and a handler for the rest of the
stack, and decides whether to call it. There is nothing to be clever about, and the
[benchmark](#benchmark) says so — three implementations of this, within thirteen per cent of
each other.

What differs is what happens the second time. A stack that keeps its position in a property is a
stack that cannot answer two requests, and cannot let a middleware call the next handler twice —
which is exactly what a retry or a cache layer does. **The position here lives in the handler
passed down**, so the same stack answers any number of requests, in any order, and a layer may
call onwards as often as it likes.

## Requirements

- PHP 8.1 or newer

## Installation

```shell
composer require quillstack/middleware
```

## Usage

### Writing one

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TimingMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $started = hrtime(true);
        $response = $handler->handle($request);

        return $response->withHeader('X-Took', (string) (hrtime(true) - $started));
    }
}
```

Anything before `$handler->handle()` sees the request on its way in; anything after sees the
response on its way out. Not calling `handle()` at all answers without the rest of the stack
ever running — which is how a rate limit refuses, and how a preflight is answered.

### Building the stack

```php
use Quillstack\Middleware\MiddlewareBuilder;

$handler = (new MiddlewareBuilder([
    ErrorMiddleware::class,
    TimingMiddleware::class,
    RoutingMiddleware::class,
], $container))->build($fallbackHandler);

$response = $handler->handle($request);
```

The first class in the list is the outermost: it sees the request first and the response last.
The fallback handler is what answers when nothing in the stack does.

### One request does not disturb another

The stack is walked by index rather than consumed, so handling a request leaves it as it was:

```php
$handler->handle($first);
$handler->handle($second);   // the same stack, all of it, again
```

That matters wherever a process handles more than one request — RoadRunner, Swoole,
FrankenPHP — and it is the sort of thing which works in testing and fails under load.

### What comes with it

| Middleware | Does |
| --- | --- |
| `Defaults\RoutingMiddleware` | matches the request to a route and calls its controller |
| `Defaults\JsonResponseMiddleware` | says the response is JSON |
| `Defaults\TrimStringsMiddleware` | takes the whitespace off what was sent |

There is no authorisation middleware here any more. There was one, and it let everything
through — a name saying authorisation is handled, over code handling nothing, is worse than
nothing at all. [quillstack/auth](https://github.com/quillstack/auth) is the real thing.

`RoutingMiddleware` puts every matched route parameter on the request as an attribute, so a
controller reads them with `$request->getAttribute('id')`. Where the path is known and the
method is not, it hands the allowed methods along under
`RoutingMiddleware::ALLOWED_METHODS`, which is what a `405` needs to name them.

## Technical documentation

| Class | What it is |
| --- | --- |
| `MiddlewareBuilder` | turns a list of class names into a handler, building each through the container |
| `MiddlewareStack` | the handler itself: immutable, walked by index |
| `MiddlewareProvider` | a stack built by adding to it rather than from a list |

`MiddlewareStack` and `MiddlewareProvider` implement `Psr\Http\Server\RequestHandlerInterface`,
so either can be handed to anything expecting a PSR-15 handler.

## Benchmark

Measured with [quillstack/benchmark](https://github.com/quillstack/benchmark) on a stack of five
layers, each adding a header, over a handler returning `200`. All three produce the same response
with the same five headers. Runs are interleaved and unconcurrent, each figure is the median of
five, and PHP is 8.5.7.

| | Version |
| --- | --- |
| quillstack/middleware | v0.9.0 |
| relay/relay | 2.1.2 |
| laminas/laminas-stratigility | 3.14.1 |

| | Per request | Relative |
| --- | --- | --- |
| **quillstack/middleware** | **3.49 µs** | — |
| relay/relay | 3.64 µs | 1.04× |
| laminas/laminas-stratigility | 3.96 µs | 1.13× |

**This is a tie, and it should be.** Running a PSR-15 stack is a loop calling `process()` and
passing a handler along; four per cent between the first two is noise, and thirteen to the third
is not a reason to choose anything. Where the difference is measured in nanoseconds, pick on
what the code does rather than what the stopwatch says — which for this one is the paragraph
above about answering a second request.

## Tests

```shell
composer test
composer test:coverage
composer stan
```

## The rest of Quillstack

This is one component of [Quillstack](https://github.com/quillstack), a PHP framework which is
as simple to use as it is strict about what it does.

- [quillstack/router](https://github.com/quillstack/router) — what the routing layer calls
- [quillstack/auth](https://github.com/quillstack/auth) — a layer which refuses rather than answers
- [quillstack/framework](https://github.com/quillstack/framework) — where the default stack is set
- [quillstack/response](https://github.com/quillstack/response) — what comes back out

## License

MIT. See [LICENSE](LICENSE).
