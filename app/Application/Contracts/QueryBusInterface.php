<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface QueryBusInterface
{
    /**
     * Ask a query and retrieve its read model response.
     */
    public function ask(object $query): mixed;
}
