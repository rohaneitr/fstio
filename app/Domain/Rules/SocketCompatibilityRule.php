<?php

declare(strict_types=1);

namespace App\Domain\Rules;

use App\Domain\Contracts\CompatibilityRuleInterface;
use App\Domain\Responses\CompatibilityResult;

final readonly class SocketCompatibilityRule implements CompatibilityRuleInterface
{
    public function evaluate(array $profiles): CompatibilityResult
    {
        $cpu = $profiles['cpu'] ?? null;
        $motherboard = $profiles['motherboard'] ?? null;

        if (! $cpu || ! $motherboard) {
            return CompatibilityResult::compatible();
        }

        $cpuSocket = $cpu->get('cpu_socket');
        $mbSocket = $motherboard->get('cpu_socket');

        if ($cpuSocket && $mbSocket && strtolower((string) $cpuSocket) !== strtolower((string) $mbSocket)) {
            return CompatibilityResult::incompatible(
                'SOCKET_MISMATCH',
                'CPU and Motherboard sockets do not match.',
                "CPU socket is {$cpuSocket} and Motherboard socket is {$mbSocket}.",
                "The selected CPU ({$cpuSocket}) cannot physically fit in the motherboard ({$mbSocket}).",
                'Select a motherboard that supports the same socket type.'
            );
        }

        return CompatibilityResult::compatible();
    }
}
