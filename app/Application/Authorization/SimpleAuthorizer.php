<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Exceptions\AuthorizationException;

final class SimpleAuthorizer implements AuthorizerInterface
{
    private bool $shouldPass = true;

    /**
     * Set mock state for testing.
     */
    public function setShouldPass(bool $shouldPass): void
    {
        $this->shouldPass = $shouldPass;
    }

    /**
     * Authorize user action.
     */
    public function authorize(string $ability): void
    {
        if (! $this->shouldPass) {
            throw new AuthorizationException("Unauthorized action for ability: {$ability}");
        }
    }
}
