<?php

declare(strict_types=1);

namespace App\Domain\Responses;

final readonly class CompatibilityResult
{
    public function __construct(
        private string $status,
        private string $severity,
        private string $code,
        private string $message,
        private ?string $technicalReason = null,
        private ?string $customerMessage = null,
        private ?string $recommendedFix = null
    ) {}

    public static function compatible(string $code = 'COMPATIBLE', string $message = 'Products are compatible.'): self
    {
        return new self('compatible', 'info', $code, $message);
    }

    public static function warning(string $code, string $message, ?string $techReason = null, ?string $custMsg = null, ?string $fix = null): self
    {
        return new self('warning', 'warning', $code, $message, $techReason, $custMsg, $fix);
    }

    public static function incompatible(string $code, string $message, ?string $techReason = null, ?string $custMsg = null, ?string $fix = null): self
    {
        return new self('incompatible', 'critical', $code, $message, $techReason, $custMsg, $fix);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTechnicalReason(): ?string
    {
        return $this->technicalReason;
    }

    public function getCustomerMessage(): ?string
    {
        return $this->customerMessage;
    }

    public function getRecommendedFix(): ?string
    {
        return $this->recommendedFix;
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'severity' => $this->severity,
            'code' => $this->code,
            'message' => $this->message,
            'technical_reason' => $this->technicalReason,
            'customer_message' => $this->customerMessage,
            'recommended_fix' => $this->recommendedFix,
        ];
    }
}
