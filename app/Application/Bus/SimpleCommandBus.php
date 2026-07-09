<?php

declare(strict_types=1);

namespace App\Application\Bus;

use App\Application\Contracts\CommandBusInterface;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final readonly class SimpleCommandBus implements CommandBusInterface
{
    public function __construct(private Container $container) {}

    /**
     * Dispatch a command DTO to its mapped Handler.
     */
    public function dispatch(object $command): mixed
    {
        $commandClass = get_class($command);
        $baseName = class_basename($commandClass);
        $baseNameWithoutDto = str_ireplace(['dto'], '', $baseName);
        $handlerClass = "App\\Application\\Handlers\\{$baseNameWithoutDto}Handler";

        if (! class_exists($handlerClass)) {
            throw new InvalidArgumentException("Handler class [{$handlerClass}] not found for command [{$commandClass}].");
        }

        $handler = $this->container->make($handlerClass);

        return $handler->handle($command);
    }
}
