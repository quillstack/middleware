<?php

declare(strict_types=1);

namespace Quillstack\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * One position in the middleware queue. Handling a request hands the next position to the
 * middleware as a new object, so nothing about the queue is changed while it runs: the
 * same stack answers a second request, and a middleware may call the next handler twice.
 */
final class MiddlewareStack implements RequestHandlerInterface
{
    /**
     * @param MiddlewareInterface[] $middleware
     */
    public function __construct(
        private array $middleware,
        private RequestHandlerInterface $fallbackHandler,
        private int $index = 0
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middleware[$this->index])) {
            return $this->fallbackHandler->handle($request);
        }

        return $this->middleware[$this->index]->process($request, $this->next());
    }

    private function next(): self
    {
        return new self($this->middleware, $this->fallbackHandler, $this->index + 1);
    }
}
