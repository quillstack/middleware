<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Unit;

use Quillstack\HttpRequest\HttpRequest;
use Quillstack\Middleware\MiddlewareProvider;
use Quillstack\Middleware\Tests\Mocks\Middlewares\ArrayLog;
use Quillstack\Middleware\Tests\Mocks\Middlewares\RecordingMiddleware;
use Quillstack\Middleware\Tests\Mocks\Middlewares\TwiceMiddleware;
use Quillstack\Middleware\Tests\Mocks\MockController;
use Quillstack\Middleware\Tests\Mocks\MockHeaders;
use Quillstack\Middleware\Tests\Mocks\MockRequest;
use Quillstack\UnitTests\AssertEqual;
use Quillstack\Uri\Uri;

/**
 * Handling a request used to consume the queue, so the second request found it empty. A
 * long running worker keeps the same objects between requests, which made this a blocker
 * for RoadRunner, Swoole and FrankenPHP.
 */
class TestMiddlewareProviderIsReusable
{
    public function __construct(private AssertEqual $assertEqual)
    {
        //
    }

    private function request(): MockRequest
    {
        return new MockRequest(HttpRequest::METHOD_GET, new Uri(), '', new MockHeaders());
    }

    private function providerWith(ArrayLog $log, string ...$names): MiddlewareProvider
    {
        $provider = new MiddlewareProvider(new MockController());

        foreach ($names as $name) {
            $provider->add(new RecordingMiddleware($name, $log));
        }

        return $provider;
    }

    public function theQueueRunsInTheOrderItWasBuilt()
    {
        $log = new ArrayLog();
        $this->providerWith($log, 'first', 'second', 'third')->handle($this->request());

        $this->assertEqual->equal(['first', 'second', 'third'], $log->entries);
    }

    public function aSecondRequestRunsTheWholeQueueAgain()
    {
        $log = new ArrayLog();
        $provider = $this->providerWith($log, 'first', 'second');

        $provider->handle($this->request());
        $provider->handle($this->request());

        $this->assertEqual->equal(['first', 'second', 'first', 'second'], $log->entries);
    }

    public function aMiddlewareMayCallTheNextHandlerTwice()
    {
        $log = new ArrayLog();
        $provider = new MiddlewareProvider(new MockController());
        $provider->add(new RecordingMiddleware('before', $log));
        $provider->add(new TwiceMiddleware());
        $provider->add(new RecordingMiddleware('after', $log));

        $provider->handle($this->request());

        $this->assertEqual->equal(['before', 'after', 'after'], $log->entries);
    }

    public function anEmptyQueueGoesStraightToTheFallbackHandler()
    {
        $log = new ArrayLog();
        $provider = $this->providerWith($log);

        $provider->handle($this->request());
        $provider->handle($this->request());

        $this->assertEqual->equal([], $log->entries);
    }
}
