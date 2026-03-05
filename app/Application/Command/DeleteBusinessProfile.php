<?php

namespace App\Application\Command;

use Domain\Business\Port\BusinessProfileRepositoryInterface;

class DeleteBusinessProfile
{
    public function __construct(
        private BusinessProfileRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $profile = $this->repository->findById($id);

        if (!$profile) {
            throw new \RuntimeException('Business profile not found.');
        }

        $this->repository->delete($id);
    }
}
