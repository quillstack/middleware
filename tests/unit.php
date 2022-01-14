<?php

declare(strict_types=1);

use Psr\Http\Server\RequestHandlerInterface;
use Quillstack\Middleware\Tests\Mocks\MockController;

$container = container();
$container->addToConfig([
    RequestHandlerInterface::class => MockController::class,
]);

return [
    \Quillstack\Middleware\Tests\Unit\TestFallbackHandlerMiddlewareProvider::class,
    \Quillstack\Middleware\Tests\Unit\TestMiddlewareProvider::class,

    \Quillstack\Middleware\Tests\Unit\TestMiddlewareBuilder::class,

    \Quillstack\Middleware\Tests\Unit\Defaults\TestAuthorizationMiddleware::class,
    \Quillstack\Middleware\Tests\Unit\Defaults\TestJsonResponseMiddleware::class,
];
