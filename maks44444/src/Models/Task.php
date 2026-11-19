<?php
declare(strict_types=1);

class Task
{
    use TimestampableTrait;

    public function __construct(
        public int $id,
        public int $userId,
        public string $title,
        public bool $completed = false,
        public ?string $deadline = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function isOverdue(): bool
    {
        if ($this->completed || $this->deadline === null || $this->deadline === '') {
            return false;
        }

        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        return $this->deadline < $today;
    }

    public function toggleCompleted(): void
    {
        $this->completed = !$this->completed;
        $this->touch();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'completed' => $this->completed,
            'deadline' => $this->deadline,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            userId: (int) ($data['user_id'] ?? 1),
            title: (string) ($data['title'] ?? ''),
            completed: (bool) ($data['completed'] ?? false),
            deadline: isset($data['deadline']) && $data['deadline'] !== '' ? (string) $data['deadline'] : null,
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }
}