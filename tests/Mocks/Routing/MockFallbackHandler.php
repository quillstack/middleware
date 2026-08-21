<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\Middleware\Tests\Mocks\MockResponse;

class MockFallbackHandler implements RequestHandlerInterface
{
    public bool $called = false;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->called = true;

        return new MockResponse();
    }
}
