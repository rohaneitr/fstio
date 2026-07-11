<?php

declare(strict_types=1);

namespace App\Application\Queries;

use App\Application\DTO\CalculatePriceDto;

final readonly class GetProductPriceQuery
{
    public function __construct(
        private CalculatePriceDto $dto
    ) {}

    public function getDto(): CalculatePriceDto
    {
        return $this->dto;
    }
}
