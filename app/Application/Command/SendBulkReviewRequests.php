<?php

namespace App\Application\Command;

use Domain\Shared\ValueObject\Email;

class SendBulkReviewRequests
{
    public function __construct(
        private SendReviewRequest $sendReviewRequest,
    ) {}

    public function execute(string $businessProfileId, array $emails): array
    {
        $results = [];

        foreach ($emails as $email) {
            $email = trim($email);
            if (empty($email)) {
                continue;
            }

            try {
                $results[] = $this->sendReviewRequest->execute($businessProfileId, $email);
            } catch (\InvalidArgumentException $e) {
                // Skip invalid emails
            }
        }

        return $results;
    }

    public function executeFromCsv(string $businessProfileId, string $csvContent): array
    {
        $lines = array_filter(explode("\n", $csvContent));
        $emails = [];

        foreach ($lines as $line) {
            $columns = str_getcsv($line);
            foreach ($columns as $col) {
                try {
                    $emails[] = (new Email(trim($col)))->value;
                } catch (\InvalidArgumentException) {
                    // Not a valid email, skip
                }
            }
        }

        return $this->execute($businessProfileId, $emails);
    }
}
