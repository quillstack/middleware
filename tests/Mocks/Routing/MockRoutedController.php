<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Reports the attributes of the request it was handed, so a test can tell whether the
 * route parameters made it through.
 */
class MockRoutedController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new MockAttributesResponse();
        $response->attributes = $request->getAttributes();

        return $response;
    }
}
