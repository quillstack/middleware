<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Defaults;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\DI\Container;
use Quillstack\Router\Dispatcher;
use Quillstack\Router\RouteInterface;

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

        // A controller may declare its own request class, which then replaces the one built
        // from globals. Either way, the route parameters have to end up on the request the
        // controller works with.
        $request = $this->withRouteParameters($controller->request ?? $request, $route);

        if (isset($controller->request)) {
            $controller->request = $request;
        }

        return $controller->handle($request);
    }

    /**
     * Puts every matched route parameter on the request as an attribute, so a controller
     * reads them with `$request->getAttribute('id')`.
     */
    private function withRouteParameters(
        ServerRequestInterface $request,
        RouteInterface $route
    ): ServerRequestInterface {
        foreach ($route->getParameters() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $request;
    }
}
