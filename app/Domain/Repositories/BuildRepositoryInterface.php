<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface BuildRepositoryInterface
{
    public function find(int $id);

    public function findByUuid(string $uuid);

    public function create(array $data);

    public function update(array $data, int $id);

    public function delete(int $id);
}
