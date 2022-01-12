<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Defaults;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\DI\Container;
use Quillstack\Router\Dispatcher;

class RoutingMiddleware implements MiddlewareInterface
{
    public Container $container;
    public Dispatcher $dispatcher;

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->dispatcher->dispatch($request);

        if (!$route->isSuccess()) {
            return $handler->handle($request);
        }

        $controller = $this->container->get(
            $route->getController()
        );

        return $controller->handle($controller->request ?? $request);
    }
}
