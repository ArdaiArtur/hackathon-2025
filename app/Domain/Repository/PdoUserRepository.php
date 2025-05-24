<?php
namespace App\Repository;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use PDO;

class PdoUserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    
    public function findByUsername(string $username): ?User
    {
        $pdo = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $pdo->bindValue(':username', $username, PDO::PARAM_STR);
        $pdo->execute();

        $data = $pdo->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new User(
            $data['id'],
            $data['username'],
            $data['password_hash'],
            $data['created_at'],
        );
    }

    public function find(mixed $id): ?User
    {
        $pdo = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $pdo->bindValue(':id', $id, PDO::PARAM_STR);
        $pdo->execute();

        $data = $pdo->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new User(
            $data['id'],
            $data['username'],
            $data['password_hash'],
            $data['created_at'],
        );
    }

    public function save(User $user): void
    {
        $pdo = $this->db->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (:username, :password_hash, :created_at)');
        $pdo->bindValue(':username', $user->username, PDO::PARAM_STR);
        $pdo->bindValue(':password_hash', $user->passwordHash, PDO::PARAM_STR);
        $pdo->bindValue(':created_at', $user->createdAt->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $pdo->execute();
    }

}