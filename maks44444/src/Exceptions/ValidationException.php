<?php
declare(strict_types=1);

class ValidationException extends TaskManagerException
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Помилка валідації даних.')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}