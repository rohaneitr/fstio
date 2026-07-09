<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface CommandBusInterface
{
    /**
     * Dispatch a command to its corresponding handler.
     */
    public function dispatch(object $command): mixed;
}
