<?php

declare(strict_types=1);

namespace Quillstack\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareBuilder
{
    public ?ContainerInterface $container;

    public function __construct(private array $middlewareClasses)
    {
        //
    }

    public function build(RequestHandlerInterface $fallbackHandler): RequestHandlerInterface
    {
        $middlewareProvider = new MiddlewareProvider($fallbackHandler);

        foreach ($this->middlewareClasses as $middlewareClass) {
            $this->add($middlewareProvider, $middlewareClass);
        }

        return $middlewareProvider;
    }

    private function add(MiddlewareProvider &$middlewareProvider, string $middlewareClass): void
    {
        $middlewareInstance = $this->container->get($middlewareClass);

        if (isset($middlewareInstance->container)) {
            $middlewareInstance->container = $this->container;
        }

        $middlewareProvider->add($middlewareInstance);
    }
}
