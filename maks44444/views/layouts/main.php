<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) ($title ?? 'Менеджер завдань'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="brand">Менеджер завдань</a>
            <?php if (!empty($_SESSION['is_logged_in'])): ?>
                <div class="user-bar">
                    <span><?= htmlspecialchars((string) ($userName ?? $_SESSION['user_name'] ?? 'Адміністратор'), ENT_QUOTES, 'UTF-8') ?></span>
                    <a href="/logout" class="btn btn-secondary">Вийти</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container main-content">
        <?= $content ?? '' ?>
    </main>
</body>
</html>