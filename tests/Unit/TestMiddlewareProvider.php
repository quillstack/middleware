<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Unit;

use Quillstack\HttpRequest\HttpRequest;
use Quillstack\Middleware\MiddlewareProvider;
use Quillstack\Middleware\Tests\Mocks\MockHeaders;
use Quillstack\Middleware\Tests\Mocks\MockMiddleware;
use Quillstack\Middleware\Tests\Mocks\MockRequest;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\Uri\Uri;

class TestMiddlewareProvider
{
    public function __construct(private MiddlewareProvider $middlewareProvider, private AssertEqual $assertEqual)
    {
        //
    }

    public function handle()
    {
        $requestHeaders = ['test' => 3];
        $this->middlewareProvider->add(new MockMiddleware());
        $response = $this->middlewareProvider->handle(
            new MockRequest(
                HttpRequest::METHOD_GET,
                new Uri(),
                '',
                new MockHeaders($requestHeaders)
            )
        );

        $this->assertEqual->equal($requestHeaders, $response->requestHeaders);
    }
}
