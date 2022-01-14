<?php

declare(strict_types=1);

namespace Quillstack\Middleware\Tests\Mocks;

use Psr\Http\Message\StreamInterface;
use Quillstack\HeaderBag\HeaderBag;
use Quillstack\Response\Response;

class MockResponse extends Response
{
    public array $requestHeaders = [];
    public bool $first = false;
    public bool $second = false;

    public function __construct(
        private int $code = 200,
        private string $reasonPhrase = '',
        private ?HeaderBag $headerBag = null,
        private string $protocolVersion = '',
        private ?StreamInterface $body = null
    ) {
        $this->headerBag = new HeaderBag();
        parent::__construct($this->code, $this->reasonPhrase, $this->headerBag, $this->protocolVersion, $this->body);
    }
}
