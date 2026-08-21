<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Unit;

use Quillstack\DI\Container;
use Quillstack\HttpRequest\HttpRequest;
use Quillstack\Middleware\MiddlewareBuilder;
use Quillstack\Middleware\Tests\Mocks\Middlewares\FirstMiddleware;
use Quillstack\Middleware\Tests\Mocks\Middlewares\SecondMiddleware;
use Quillstack\Middleware\Tests\Mocks\MockController;
use Quillstack\Middleware\Tests\Mocks\MockHeaders;
use Quillstack\Middleware\Tests\Mocks\MockRequest;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\Types\AssertBoolean;
use Quillstack\Uri\Uri;

class TestMiddlewareBuilder
{
    private MiddlewareBuilder $middlewareBuilder;

    public function __construct(private AssertBoolean $assertBoolean, Container $container)
    {
        $this->middlewareBuilder = new MiddlewareBuilder([
            FirstMiddleware::class,
            SecondMiddleware::class,
        ], $container);
    }

    public function build()
    {
        $middlewareProvider = $this->middlewareBuilder->build(new MockController());
        $response = $middlewareProvider->handle(
            new MockRequest(
                HttpRequest::METHOD_GET,
                new Uri(),
                '',
                new MockHeaders()
            )
        );

        $this->assertBoolean->isTrue($response->first);
        $this->assertBoolean->isTrue($response->second);
    }
}
