<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Authorization\AuthorizerInterface;
use App\Application\DTO\RegisterCompatibilityRuleDto;
use App\Application\Exceptions\ValidationException;
use App\Application\Responses\CommandResponse;
use App\Domain\Factories\CompatibilityFactory;
use App\Domain\Repositories\CompatibilityRepositoryInterface;
use App\Domain\Specifications\CompatibilitySpecification;
use Illuminate\Support\Facades\DB;

final readonly class RegisterCompatibilityRuleHandler
{
    public function __construct(
        private AuthorizerInterface $authorizer,
        private CompatibilityRepositoryInterface $repository,
        private CompatibilityFactory $factory,
        private CompatibilitySpecification $specification
    ) {}

    public function handle(RegisterCompatibilityRuleDto $dto): CommandResponse
    {
        // 1. Authorize
        $this->authorizer->authorize('register_compatibility');

        // 2. Transaction
        return DB::transaction(function () use ($dto) {
            $rule = $this->factory->create(
                $dto->getParentProductId(),
                $dto->getChildProductId(),
                $dto->getType()
            );

            // 3. Domain specification check
            if (! $this->specification->isSatisfiedBy($rule)) {
                throw new ValidationException('Compatibility rule fails domain requirements.');
            }

            $saved = $this->repository->create([
                'parent_product_id' => $rule->parent_product_id,
                'child_product_id' => $rule->child_product_id,
                'rule_type' => $rule->rule_type,
            ]);

            return CommandResponse::success(
                ['id' => $saved->id],
                'Compatibility rule registered successfully.'
            );
        });
    }
}
