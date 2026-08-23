<?php

declare(strict_types=1);

use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\Middleware\Tests\Mocks\MockController;

return [
    'config' => [
        RequestHandlerInterface::class => MockController::class,
    ],
    'tests' => [
        \Quillstack\Middleware\Tests\Unit\TestFallbackHandlerMiddlewareProvider::class,
        \Quillstack\Middleware\Tests\Unit\TestMiddlewareProvider::class,
        \Quillstack\Middleware\Tests\Unit\TestMiddlewareProviderIsReusable::class,

        \Quillstack\Middleware\Tests\Unit\TestMiddlewareBuilder::class,

        \Quillstack\Middleware\Tests\Unit\Defaults\TestJsonResponseMiddleware::class,
        \Quillstack\Middleware\Tests\Unit\Defaults\TestRoutingMiddleware::class,
    ],
];
