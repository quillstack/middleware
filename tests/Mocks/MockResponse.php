<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks;

use Quillstack\Response\Response;

class MockResponse extends Response
{
    public array $requestHeaders = [];
}
