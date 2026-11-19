<?php
declare(strict_types=1);

$page = (int) ($page ?? 1);
$errors = $errors ?? [];
$oldInput = $oldInput ?? [];
$oldTitle = (string) ($oldInput['title'] ?? '');
$oldDeadline = (string) ($oldInput['deadline'] ?? '');
?>
<section class="task-form-page">
    <div class="page-header">
        <h1>Нове завдання</h1>
        <a href="/?page=<?= $page ?>" class="btn btn-secondary">Назад до списку</a>
    </div>

    <?php if ($errors !== []): ?>
        <div class="alert alert-error">
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/tasks/create?page=<?= $page ?>" class="task-form">
        <div class="form-group">
            <label for="title">Назва завдання</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?= htmlspecialchars($oldTitle, ENT_QUOTES, 'UTF-8') ?>"
                required
                maxlength="255"
            >
        </div>

        <div class="form-group">
            <label for="deadline">Дедлайн</label>
            <input
                type="date"
                id="deadline"
                name="deadline"
                value="<?= htmlspecialchars($oldDeadline, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Зберегти</button>
            <a href="/?page=<?= $page ?>" class="btn btn-secondary">Скасувати</a>
        </div>
    </form>
</section>