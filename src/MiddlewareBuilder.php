<?php

declare(strict_types=1);

namespace Quillstack\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareBuilder
{
    /**
     * @param string[] $middlewareClasses
     */
    public function __construct(
        private readonly array $middlewareClasses,
        private readonly ContainerInterface $container
    ) {
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

    private function add(MiddlewareProvider $middlewareProvider, string $middlewareClass): void
    {
        $middlewareProvider->add(
            $this->container->get($middlewareClass)
        );
    }
}
