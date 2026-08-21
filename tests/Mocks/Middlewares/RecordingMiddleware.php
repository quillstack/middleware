<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Writes its name into a shared list, so a test can read back the order the queue ran in
 * and how many times each middleware was reached.
 */
class RecordingMiddleware implements MiddlewareInterface
{
    public function __construct(private string $name, private ArrayLog $log)
    {
        //
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->log->entries[] = $this->name;

        return $handler->handle($request);
    }
}
