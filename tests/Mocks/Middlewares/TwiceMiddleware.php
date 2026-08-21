<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Calls the next handler twice, which a middleware retrying a request would do.
 */
class TwiceMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handler->handle($request);

        return $handler->handle($request);
    }
}
