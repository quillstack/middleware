<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Unit;

use Quillstack\HttpRequest\HttpRequest;
use Quillstack\Middleware\MiddlewareProvider;
use Quillstack\Middleware\Tests\Mocks\MockHeaders;
use Quillstack\Middleware\Tests\Mocks\MockRequest;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\Uri\Uri;

class TestFallbackHandlerMiddlewareProvider
{
    public function __construct(private MiddlewareProvider $middlewareProvider, private AssertEqual $assertEqual)
    {
        //
    }


    public function handle()
    {
        $response = $this->middlewareProvider->handle(
            new MockRequest(
                HttpRequest::METHOD_GET,
                new Uri(),
                '',
                new MockHeaders(['test' => 3])
            )
        );

        $this->assertEqual->equal([], $response->requestHeaders);
    }
}
