<?php

declare(strict_types=1);

namespace QuillStack\Middleware\Defaults;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use QuillStack\DI\Container;
use QuillStack\Router\Dispatcher;

final class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var Container
     */
    public Container $container;

    /**
     * @var Dispatcher
     */
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
