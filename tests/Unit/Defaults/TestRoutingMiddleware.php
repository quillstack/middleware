<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Unit\Defaults;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Quillstack\DI\Container;
use Quillstack\HttpRequest\HttpRequest;
use Quillstack\Middleware\Defaults\RoutingMiddleware;
use Quillstack\Middleware\Tests\Mocks\MockHeaders;
use Quillstack\Middleware\Tests\Mocks\MockRequest;
use Quillstack\Middleware\Tests\Mocks\Routing\MockFallbackHandler;
use Quillstack\Middleware\Tests\Mocks\Routing\MockRoutedController;
use Quillstack\Middleware\Tests\Mocks\Routing\MockRoutedControllerWithRequest;
use Quillstack\Router\Dispatcher;
use Quillstack\Router\Router;
use Quillstack\ServerRequest\Factory\ServerRequest\ServerRequestFromGlobalsFactory;
use Quillstack\Stream\InputStream;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\UnitTests\Types\AssertBoolean;
use Quillstack\Uri\Factory\UriFactory;
use Quillstack\Uri\Uri;

class TestRoutingMiddleware
{
    public function __construct(
        private AssertEqual $assertEqual,
        private AssertBoolean $assertBoolean
    ) {
        //
    }

    /**
     * Builds a container holding a router with the given routes, and a request for the
     * given method and URI.
     */
    private function middlewareFor(string $method, string $uri, callable $routes, array $extra = []): array
    {
        $container = new Container($extra + [
            StreamInterface::class => InputStream::class,
            UriFactoryInterface::class => UriFactory::class,
            ServerRequestFromGlobalsFactory::class => [
                'server' => [
                    'REQUEST_METHOD' => $method,
                    'HTTP_HOST' => 'localhost',
                    'REQUEST_URI' => $uri,
                    'SERVER_PROTOCOL' => '1.1',
                ],
            ],
        ]);

        $routes($container->get(Router::class));

        $middleware = new RoutingMiddleware($container, $container->get(Dispatcher::class));

        $request = $container->get(ServerRequestFromGlobalsFactory::class)->createServerRequest();

        return [$middleware, $request];
    }

    public function routeParametersReachTheController()
    {
        [$middleware, $request] = $this->middlewareFor(
            'GET',
            '/users/13/posts/7',
            fn (Router $router) => $router->get('/users/:user/posts/:post', MockRoutedController::class)
        );

        $response = $middleware->process($request, new MockFallbackHandler());

        $this->assertEqual->equal(['user' => '13', 'post' => '7'], $response->attributes);
    }

    public function routeParametersReachACustomRequestClass()
    {
        // A controller declaring its own request gets it from the framework's request
        // factory, which lives outside this package, so it is handed over ready made.
        $controller = new MockRoutedControllerWithRequest();
        $controller->request = new MockRequest(HttpRequest::METHOD_GET, new Uri(), '', new MockHeaders());

        [$middleware, $request] = $this->middlewareFor(
            'GET',
            '/users/13/posts/7',
            fn (Router $router) => $router->get('/users/:user/posts/:post', MockRoutedControllerWithRequest::class),
            [MockRoutedControllerWithRequest::class => $controller]
        );

        $response = $middleware->process($request, new MockFallbackHandler());

        $this->assertEqual->equal(['user' => '13', 'post' => '7'], $response->attributes);
    }

    public function aRouteWithoutParametersAddsNoAttributes()
    {
        [$middleware, $request] = $this->middlewareFor(
            'GET',
            '/status',
            fn (Router $router) => $router->get('/status', MockRoutedController::class)
        );

        $response = $middleware->process($request, new MockFallbackHandler());

        $this->assertEqual->equal([], $response->attributes);
    }

    public function anUnmatchedRequestGoesToTheNextHandler()
    {
        [$middleware, $request] = $this->middlewareFor(
            'GET',
            '/nothing-here',
            fn (Router $router) => $router->get('/status', MockRoutedController::class)
        );

        $handler = new MockFallbackHandler();
        $middleware->process($request, $handler);

        $this->assertBoolean->isTrue($handler->called);
    }
}
