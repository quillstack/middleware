<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\Middleware\Tests\Mocks\MockRequest;

/**
 * Declares its own request class, the way a Quillstack controller does. The route
 * parameters have to end up on that request too.
 */
class MockRoutedControllerWithRequest implements RequestHandlerInterface
{
    public MockRequest $request;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new MockAttributesResponse();
        $response->attributes = $this->request->getAttributes();

        return $response;
    }
}
