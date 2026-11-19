<?php
declare(strict_types=1);

interface TaskStorageInterface
{
    public function findAllByUser(int $userId, int $page = 1, int $perPage = 5): array;

    public function countByUser(int $userId): int;

    public function countCompletedByUser(int $userId): int;

    public function countOverdueByUser(int $userId): int;

    public function findById(int $userId, int $id): ?Task;

    public function add(int $userId, string $title, ?string $deadline = null): Task;

    public function complete(int $userId, int $id): void;

    public function delete(int $userId, int $id): void;
}