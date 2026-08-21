<?php

declare(strict_types=1);

namespace Quillstack\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareProvider implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middleware = [];

    public function __construct(private RequestHandlerInterface $fallbackHandler)
    {
        //
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new MiddlewareStack($this->middleware, $this->fallbackHandler))->handle($request);
    }
}
