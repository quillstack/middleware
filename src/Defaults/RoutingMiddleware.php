<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Defaults;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\Router\Dispatcher;
use Quillstack\Router\RouteInterface;
use Quillstack\Router\Routes\MethodNotAllowedRoute;

class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * The request attribute naming the methods a known path answers to, so whatever ends up
     * answering can say `405` and name them. Prefixed, because route parameters become
     * attributes too and an application names those.
     */
    public const ALLOWED_METHODS = 'quillstack.allowed-methods';

    /**
     * A route names its controller, so the container is what turns that name into an
     * object. It is asked for one thing only, which is why the PSR-11 interface is enough.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly Dispatcher $dispatcher
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->dispatcher->dispatch($request);

        if ($route instanceof MethodNotAllowedRoute) {
            return $handler->handle(
                $request->withAttribute(self::ALLOWED_METHODS, $route->getAllowedMethods())
            );
        }

        if (!$route->isSuccess()) {
            return $handler->handle($request);
        }

        /** @var RequestHandlerInterface $controller */
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
