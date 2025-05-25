<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;
use Psr\Log\LoggerInterface;

class PdoExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private LoggerInterface $logger,
    ) {}

    /**
     * @throws Exception
     */
    public function find(int $id): ?Expense
    {
        $query = 'SELECT * FROM expenses WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return $this->createExpenseFromData($data);
    }
    public function countUsersExpens(int $userId): int
    {
        $query = 'SELECT Count(*) as count FROM expenses WHERE user_id = :userId';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['userId' => $userId]);
        $data = $statement->fetch();
        if (false === $data) {
            return 0;
        }

        return $data['count'];
    }

    public function save(Expense $expense): void
    {
        $query = 'INSERT INTO expenses (user_id, date, category, amount_cents, description)VALUES (:user_id, :date, :category, :amount_cents, :description)';
        $statement = $this->pdo->prepare($query);
        $statement->execute([
            'user_id' => $expense->userId,
            'date' => $expense->date->format('Y-m-d H:i:s'),
            'category' => $expense->category,
            'amount_cents' => $expense->amountCents,
            'description' => $expense->description,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        $query = 'SELECT * FROM expenses WHERE user_id = :userId';
        $params = [];
        $params['userId'] = $criteria['user_id'];

        if (isset($criteria['year']) && $criteria['year'] != 0) {
            $query .= " AND strftime('%Y', date) = :year";
            $params['year'] = $criteria['year'];
        }

        if (isset($criteria['month']) && $criteria['month'] != 0) {
            $query .= " AND strftime('%m', date) = :month";
            $params['month'] = str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT);
        }

        $query .= ' ORDER BY date DESC LIMIT :from, :limit';
        $params['limit'] = $limit;
        $params['from'] = $from;
        // $this->logger->info($query);
        // $this->logger->info("params", $params);
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $results = $statement->fetchAll();
        return $results;
    }



    public function countBy(array $criteria): int
    {
        // TODO: Implement countBy() method.
        return 0;
    }

    public function listExpenditureYears(int $userId): array
    {
        $query = "SELECT  DISTINCT strftime('%Y', date) as year FROM expenses WHERE user_id = :user_id ORDER BY year DESC";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['user_id' => $userId]);
        $data = $statement->fetchAll();
        if (false === $data) {
            return [];
        }
        return $data;
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        $query = 'SELECT SUM(amount_cents) as total,category FROM expenses WHERE user_id = :userId';
        $params = [];
        $params['userId'] = $criteria['user_id'];

        if (isset($criteria['year']) && $criteria['year'] != 0) {
            $query .= " AND strftime('%Y', date) = :year";
            $params['year'] = $criteria['year'];
        }

        if (isset($criteria['month']) && $criteria['month'] != 0) {
            $query .= " AND strftime('%m', date) = :month";
            $params['month'] = str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT);
        }

        $query .= " GROUP BY category";
        // $this->logger->info($query);
        // $this->logger->info("params", $params);
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $result = $statement->fetchAll();
        return $result;
    }

    public function averageAmountsByCategory(array $criteria): array
    {
        // TODO: Implement averageAmountsByCategory() method.
        return [];
    }

    public function sumAmounts(array $criteria): float
    {
        $query = 'SELECT SUM(amount_cents) as total FROM expenses WHERE user_id = :userId';
        $params = [];
        $params['userId'] = $criteria['user_id'];

        if (isset($criteria['year']) && $criteria['year'] != 0) {
            $query .= " AND strftime('%Y', date) = :year";
            $params['year'] = $criteria['year'];
        }

        if (isset($criteria['month']) && $criteria['month'] != 0) {
            $query .= " AND strftime('%m', date) = :month";
            $params['month'] = str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT);
        }

        // $this->logger->info($query);
        // $this->logger->info("params", $params);
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    public function update(array  $expense, int $id): bool
    {

        $query = 'UPDATE expenses SET ';
        $params = [];
        $hasUpdates = false;

        if (isset($expense['date'])) {
            if ($hasUpdates) $query .= ', ';
            $query .= 'date = :date';
            $params['date'] = $expense['date'];
            $hasUpdates = true;
        }

        if (isset($expense['category'])) {
            if ($hasUpdates) $query .= ', ';
            $query .= 'category = :category';
            $params['category'] = $expense['category'];
            $hasUpdates = true;
        }

        if (isset($expense['amount_cents'])) {
            if ($hasUpdates) $query .= ', ';
            $query .= 'amount_cents = :amount_cents';
            $params['amount_cents'] = $expense['amount_cents'];
            $hasUpdates = true;
        }

        if (isset($expense['description'])) {
            if ($hasUpdates) $query .= ', ';
            $query .= 'description = :description';
            $params['description'] = $expense['description'];
            $hasUpdates = true;
        }

        if (!$hasUpdates) {
            return false; 
        }

        $query .= ' WHERE id = :id';
        $params['id'] = $id;

        $statement = $this->pdo->prepare($query);

        return $statement->execute($params);
    }

    /**
     * @throws Exception
     */
    private function createExpenseFromData(mixed $data): Expense
    {
        return new Expense(
            $data['id'],
            $data['user_id'],
            new DateTimeImmutable($data['date']),
            $data['category'],
            $data['amount_cents'],
            $data['description'],
        );
    }
}
