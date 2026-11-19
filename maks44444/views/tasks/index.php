<?php
declare(strict_types=1);

/** @var Task[] $tasks */
$tasks = $tasks ?? [];
$page = (int) ($page ?? 1);
$totalTasks = (int) ($totalTasks ?? 0);
$totalPages = (int) ($totalPages ?? 1);
$completedCount = (int) ($completedCount ?? 0);
$overdueCount = (int) ($overdueCount ?? 0);
$formErrors = $formErrors ?? [];
$feedbackMessage = $feedbackMessage ?? null;
?>
<section class="tasks-page">
    <div class="page-header">
        <h1>Завдання</h1>
        <a href="/tasks/create?page=<?= $page ?>" class="btn btn-primary">+ Нове завдання</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Усього</span>
            <span class="stat-value"><?= $totalTasks ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Виконано</span>
            <span class="stat-value"><?= $completedCount ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Прострочено</span>
            <span class="stat-value"><?= $overdueCount ?></span>
        </div>
    </div>

    <?php if ($feedbackMessage !== null && $feedbackMessage !== ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars((string) $feedbackMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($formErrors !== []): ?>
        <div class="alert alert-error">
            <ul class="error-list">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($tasks === []): ?>
        <div class="empty-state">
            <p>Завдань поки немає.</p>
            <a href="/tasks/create?page=<?= $page ?>" class="btn btn-primary">Створити перше завдання</a>
        </div>
    <?php else: ?>
        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <?php
                $isCompleted = $task->completed;
                $isOverdue = $task->isOverdue();
                $itemClasses = ['task-item'];
                if ($isCompleted) {
                    $itemClasses[] = 'is-completed';
                }
                if ($isOverdue) {
                    $itemClasses[] = 'is-overdue';
                }
                ?>
                <li class="<?= implode(' ', $itemClasses) ?>">
                    <div class="task-main">
                        <span class="task-title">
                            <?= htmlspecialchars($task->title, ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <div class="task-meta">
                            <?php if ($task->deadline !== null && $task->deadline !== ''): ?>
                                <span class="task-deadline">
                                    Дедлайн: <?= htmlspecialchars($task->deadline, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($isCompleted): ?>
                                <span class="badge badge-done">Виконано</span>
                            <?php elseif ($isOverdue): ?>
                                <span class="badge badge-overdue">Прострочено</span>
                            <?php else: ?>
                                <span class="badge badge-pending">В роботі</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="task-actions">
                        <form method="POST" action="/tasks/complete?page=<?= $page ?>" class="inline-form">
                            <input type="hidden" name="id" value="<?= $task->id ?>">
                            <button type="submit" class="btn btn-secondary">
                                <?= $isCompleted ? 'Повернути' : 'Виконати' ?>
                            </button>
                        </form>

                        <form method="POST" action="/tasks/delete?page=<?= $page ?>" class="inline-form">
                            <input type="hidden" name="id" value="<?= $task->id ?>">
                            <button type="submit" class="btn btn-danger">Видалити</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Навігація по сторінках">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="page-link is-active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="/?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>