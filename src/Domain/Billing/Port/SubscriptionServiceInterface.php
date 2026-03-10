<?php

declare(strict_types=1);

namespace Domain\Billing\Port;

interface SubscriptionServiceInterface
{
    public function startTrial(string $tenantId): void;

    public function createCheckoutSession(string $tenantId, string $successUrl, string $cancelUrl): string;

    public function cancelSubscription(string $tenantId): void;

    public function resumeSubscription(string $tenantId): void;

    public function isActive(string $tenantId): bool;

    public function isOnTrial(string $tenantId): bool;

    public function isCancelled(string $tenantId): bool;

    public function getStatus(string $tenantId): array;
}
