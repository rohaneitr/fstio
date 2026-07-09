<?php

declare(strict_types=1);

namespace App\Application\Bus;

use App\Application\Contracts\QueryBusInterface;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final readonly class SimpleQueryBus implements QueryBusInterface
{
    public function __construct(private Container $container) {}

    /**
     * Ask a query and execute its Handler.
     */
    public function ask(object $query): mixed
    {
        $queryClass = get_class($query);
        $handlerClass = $queryClass.'Handler';

        if (! class_exists($handlerClass)) {
            throw new InvalidArgumentException("Handler class [{$handlerClass}] not found for query [{$queryClass}].");
        }

        $handler = $this->container->make($handlerClass);

        return $handler->handle($query);
    }
}
