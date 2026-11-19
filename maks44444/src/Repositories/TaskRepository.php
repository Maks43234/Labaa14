<?php
declare(strict_types=1);

class TaskRepository implements TaskStorageInterface
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findAllByUser(int $userId, int $page = 1, int $perPage = 5): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, title, completed, deadline, created_at, updated_at
             FROM tasks
             WHERE user_id = :user_id
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $tasks = [];
        foreach ($stmt->fetchAll() as $row) {
            $tasks[] = Task::fromArray($row);
        }

        return $tasks;
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countCompletedByUser(int $userId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND completed = 1'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countOverdueByUser(int $userId): int
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM tasks
             WHERE user_id = :user_id
               AND completed = 0
               AND deadline IS NOT NULL
               AND deadline != \'\'
               AND deadline < :today'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function findById(int $userId, int $id): ?Task
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, title, completed, deadline, created_at, updated_at
             FROM tasks
             WHERE id = :id AND user_id = :user_id
             LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row !== false ? Task::fromArray($row) : null;
    }

    public function add(int $userId, string $title, ?string $deadline = null): Task
    {
        $cleanTitle = trim($title);
        $errors = [];

        if ($cleanTitle === '') {
            $errors[] = 'Назва завдання не може бути порожньою.';
        } elseif (mb_strlen($cleanTitle) > 255) {
            $errors[] = 'Назва завдання не може перевищувати 255 символів.';
        }

        $cleanDeadline = $deadline !== null ? trim($deadline) : null;
        if ($cleanDeadline === '') {
            $cleanDeadline = null;
        }

        if ($cleanDeadline !== null) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $cleanDeadline);
            if ($date === false || $date->format('Y-m-d') !== $cleanDeadline) {
                $errors[] = 'Некоректний формат дати дедлайну (очікується РРРР-ММ-ДД).';
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            'INSERT INTO tasks (user_id, title, completed, deadline, created_at, updated_at)
             VALUES (:user_id, :title, :completed, :deadline, :created_at, :updated_at)'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $cleanTitle, PDO::PARAM_STR);
        $stmt->bindValue(':completed', 0, PDO::PARAM_INT);
        $stmt->bindValue(':deadline', $cleanDeadline, $cleanDeadline === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':created_at', $now, PDO::PARAM_STR);
        $stmt->bindValue(':updated_at', $now, PDO::PARAM_STR);
        $stmt->execute();

        $id = (int) $this->pdo->lastInsertId();

        return new Task(
            id: $id,
            userId: $userId,
            title: $cleanTitle,
            completed: false,
            deadline: $cleanDeadline,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public function complete(int $userId, int $id): void
    {
        if ($id <= 0) {
            throw new ValidationException(['Некоректний ідентифікатор завдання.']);
        }

        $task = $this->findById($userId, $id);
        if ($task === null) {
            throw new TaskNotFoundException('Завдання не знайдено.');
        }

        $task->toggleCompleted();

        $stmt = $this->pdo->prepare(
            'UPDATE tasks
             SET completed = :completed, updated_at = :updated_at
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->bindValue(':completed', $task->completed ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':updated_at', $task->updatedAt, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $userId, int $id): void
    {
        if ($id <= 0) {
            throw new ValidationException(['Некоректний ідентифікатор завдання.']);
        }

        $stmt = $this->pdo->prepare(
            'DELETE FROM tasks WHERE id = :id AND user_id = :user_id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new TaskNotFoundException('Завдання не знайдено.');
        }
    }
}