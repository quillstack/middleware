<?php

declare(strict_types=1);

namespace QuillStack\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareBuilder
{
    /**
     * @var ContainerInterface|null
     */
    public ?ContainerInterface $container;

    /**
     * @var array
     */
    private array $middlewareClasses;

    /**
     * @param array $middlewareClasses
     */
    public function __construct(array $middlewareClasses)
    {
        $this->middlewareClasses = $middlewareClasses;
    }

    /**
     * @param RequestHandlerInterface $fallbackHandler
     *
     * @return RequestHandlerInterface
     */
    public function build(RequestHandlerInterface $fallbackHandler): RequestHandlerInterface
    {
        $middlewareProvider = new MiddlewareProvider($fallbackHandler);

        foreach ($this->middlewareClasses as $middlewareClass) {
            $this->add($middlewareProvider, $middlewareClass);
        }

        return $middlewareProvider;
    }

    /**
     * @param MiddlewareProvider $middlewareProvider
     * @param string $middlewareClass
     */
    private function add(MiddlewareProvider &$middlewareProvider, string $middlewareClass): void
    {
        $middlewareInstance = $this->container->get($middlewareClass);

        if (isset($middlewareInstance->container)) {
            $middlewareInstance->container = $this->container;
        }

        $middlewareProvider->add($middlewareInstance);
    }
}
