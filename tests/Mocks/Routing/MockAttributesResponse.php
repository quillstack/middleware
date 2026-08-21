<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks\Routing;

use Quillstack\Middleware\Tests\Mocks\MockResponse;

class MockAttributesResponse extends MockResponse
{
    public array $attributes = [];
}
