<?php
declare(strict_types=1);

trait TimestampableTrait
{
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function touch(): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        if ($this->createdAt === null || $this->createdAt === '') {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
    }
}