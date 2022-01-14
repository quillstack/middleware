<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Unit\Defaults;

use Psr\Http\Message\ResponseInterface;
use Quillstack\HttpRequest\HttpRequest;
use Quillstack\Middleware\Defaults\AuthorizationMiddleware;
use Quillstack\Middleware\MiddlewareProvider;
use Quillstack\Middleware\Tests\Mocks\MockHeaders;
use Quillstack\Middleware\Tests\Mocks\MockRequest;
use Quillstack\UnitTests\Types\AssertObject;
use Quillstack\Uri\Uri;

class TestAuthorizationMiddleware
{
    public function __construct(private MiddlewareProvider $middlewareProvider, private AssertObject $assertObject)
    {
        //
    }

    public function handle()
    {
        $this->middlewareProvider->add(new AuthorizationMiddleware());
        $response = $this->middlewareProvider->handle(
            new MockRequest(
                HttpRequest::METHOD_GET,
                new Uri(),
                '',
                new MockHeaders()
            )
        );

        $this->assertObject->instanceOf(ResponseInterface::class, $response);
    }
}
