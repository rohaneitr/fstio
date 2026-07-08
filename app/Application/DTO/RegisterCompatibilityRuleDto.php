<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Enums\CompatibilityType;

final readonly class RegisterCompatibilityRuleDto
{
    public function __construct(
        private int $parentProductId,
        private int $childProductId,
        private CompatibilityType $type
    ) {}

    public function getParentProductId(): int
    {
        return $this->parentProductId;
    }

    public function getChildProductId(): int
    {
        return $this->childProductId;
    }

    public function getType(): CompatibilityType
    {
        return $this->type;
    }
}
