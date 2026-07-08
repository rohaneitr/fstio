<?php

declare(strict_types=1);

namespace App\Application\Responses;

final readonly class CommandResponse
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private bool $success,
        private array $payload = [],
        private ?string $message = null
    ) {}

    public static function success(array $payload = [], ?string $message = null): self
    {
        return new self(true, $payload, $message);
    }

    public static function failure(string $message): self
    {
        return new self(false, [], $message);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
