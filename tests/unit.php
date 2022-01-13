<?php

declare(strict_types=1);

$container = container();
$container->addToConfig([
    \Psr\Http\Server\RequestHandlerInterface::class => \Quillstack\Middleware\Tests\Mocks\MockController::class,
]);

return [
    \Quillstack\Middleware\Tests\Unit\TestMiddlewareProvider::class,
];
