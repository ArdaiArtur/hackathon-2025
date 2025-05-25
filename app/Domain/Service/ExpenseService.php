<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private LoggerInterface $logger,
    ) {}

    public function list(int $userId, int $year, int $month, int $pageNumber, int $pageSize): array
    {
        $criteria = [
            'user_id' => $userId,
            'year' => $year,
            'month' => $month,
        ];

        $from = ($pageNumber - 1) * $pageSize;
        $data = [
            'years' => $this->expenses->listExpenditureYears($userId),
            'expenses' => $this->expenses->findBy($criteria, $from, $pageSize),
        ];
        return $data;
    }

    public function create(
        int $userId,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        $expense = new Expense(null, $userId, $date, $category, (int)($amount * 100), $description);
        $this->expenses->save($expense);
    }

    public function update(
        Expense $expense,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        $fields = [];

        if ($expense->amountCents !== (int)($amount * 100)) {
            $fields['amount_cents'] = (int)($amount * 100);
        }
        if ($expense->description !== $description) {
            $fields['description'] = $description;
        }
        if ($expense->date != $date) {
            $fields['date'] = $date->format('Y-m-d H:i:s');
        }
        if ($expense->category !== $category) {
            $fields['category'] = $category;
        }

        if (!empty($fields)) {
            $this->expenses->update($fields, $expense->id,);
        }
    }

    public function importFromCsv(int $userId, UploadedFileInterface $csvFile): int
    {
        $csvContent = $csvFile->getStream()->getContents();
        $lines = explode("\n", trim($csvContent));
        $columns = ['date', 'amount', 'description', 'category'];


        $imported = 0;

        for ($i = 0; $i < count($lines); $i++) {

            $row = str_getcsv($lines[$i]);
            $data = array_combine($columns, $row);
            $this->create($userId, (float)$data['amount'], $data['description'], new DateTimeImmutable($data['date']), $data['category']);
            $imported++;
        }

        return $imported;
    }

    public function find(int $id): ?Expense
    {
        return $this->expenses->find($id);
    }

    public function count(int $userId): int
    {
        return $this->expenses->countUsersExpens($userId);
    }

    public function delete(int $id): void
    {
        $this->expenses->delete($id);
    }
}
