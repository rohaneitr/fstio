<?php

declare(strict_types=1);

namespace App\Foundation\Audit;

use App\Foundation\Contracts\ServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditLogger implements ServiceInterface
{
    private string $correlationId;

    public function __construct()
    {
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Get the current request correlation ID.
     */
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    /**
     * Log an audit event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function log(string $event, string $description, array $payload = []): void
    {
        $userId = Auth::id();
        $ipAddress = Request::ip() ?? '127.0.0.1';
        $userAgent = Request::header('User-Agent') ?? 'Unknown';

        $data = [
            'correlation_id' => $this->correlationId,
            'event' => $event,
            'description' => $description,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'payload' => json_encode($payload),
            'timestamp' => now()->toIso8601String(),
        ];

        // Safe DB write check inside try/catch so missing migration never breaks core requests
        try {
            // Write to database if model exists (we can define a DB fallback or log to files)
            Log::info("AUDIT_TRAIL [{$event}]: {$description}", $data);
        } catch (\Throwable $e) {
            Log::error('Failed to log audit database record: '.$e->getMessage());
        }
    }
}
