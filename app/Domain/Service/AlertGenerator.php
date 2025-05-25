<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use Psr\Log\LoggerInterface;

class AlertGenerator
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private LoggerInterface $logger,
    ) {}
    public function generate(int $userId, int $year, int $month): array
    {
        $totals = $this->expenses->sumAmountsByCategory(
            [
                "user_id" => $userId,
                "year" => $year,
                "month" => $month,
            ]
        );
       // $this->logger->info('Logging array in context' . json_encode($total));
        $alerts = [];
        $json = $_ENV["BUGETS"] ?? "{}";
        $budgets = json_decode($json, true) ?? [];
        $this->logger->info('Logging array in context' . json_encode($budgets));
        foreach ($totals as $total ) {
           // $this->logger->info('Logging array in context' .$category.$entry['total']);
            $cents = $total['total'];
            $spentEuros = $cents / 100;
            $normalizedCategory = strtolower((string)$total['category']);
            $budget = $budgets[$normalizedCategory] ?? null;

            if ($budget === null) {
                continue;
            }

            if ($spentEuros > $budget) {
                $over = $spentEuros - $budget;
                $alerts[] = "Budget exceeded in category {$total['category']}: {$spentEuros} € spent out of {$budget} € allocated. You went over by {$over} €.";
            }
        }
        return $alerts;
    }
}
