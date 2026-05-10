<?php

namespace App\Exceptions;

use RuntimeException;

class GeminiGenerationException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?int $status = null,
        private readonly ?string $responseBody = null
    ) {
        parent::__construct($message);
    }

    public function status(): ?int
    {
        return $this->status;
    }

    public function responseBody(): ?string
    {
        return $this->responseBody;
    }

    public function isQuotaExceeded(): bool
    {
        return $this->status === 429;
    }
}
