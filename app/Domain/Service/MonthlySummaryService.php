<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use Psr\Log\LoggerInterface;

class MonthlySummaryService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private LoggerInterface $logger,
    ) {}

    public function computeTotalExpenditure(int $userId, int $year = 0, int $month = 0): float
    {
       return $this->expenses->sumAmounts([
            'user_id'=>$userId,
            'year'=>$year,
            'month'=>$month,
    ]);
       
    }

    public function getYears(int $userId, int $year = 0, int $month = 0): array
    {
        
       return $this->expenses->listExpenditureYears($userId);
       
    }

    public function computePerCategoryTotals(int $userId, int $year, int $month): array
    {
       return $this->expenses->sumAmountsByCategory([
            'user_id'=>$userId,
            'year'=>$year,
            'month'=>$month,
    ]);
    }

    public function computePerCategoryAverages(int $userId, int $year, int $month): array
    {
         return $this->expenses->averageAmountsByCategory([
            'user_id'=>$userId,
            'year'=>$year,
            'month'=>$month,
    ]);
    }
}
